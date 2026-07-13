<?php

namespace App\Http\Controllers;

use App\Mail\CotisationMail;
use App\Models\Cotisation;
use App\Models\Membre;
use App\Models\Saison;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class CotisationController extends Controller
{
    public function index(Request $request): Response
    {
        $saison = Saison::find($request->saison_id) ?? Saison::active();

        $cotisations = Cotisation::with(['membre', 'saison'])
            ->when($saison, fn($q) => $q->where('saison_id', $saison->id))
            ->when($request->statut, fn($q, $s) => $q->where('statut', $s))
            ->when($request->search, fn($q, $s) =>
                $q->whereHas('membre', fn($m) =>
                    $m->where('nom', 'like', "%{$s}%")
                      ->orWhere('prenom', 'like', "%{$s}%")
                )
            )
            ->orderByRaw("FIELD(statut, 'brouillon', 'envoye', 'paye', 'annule')")
            ->paginate(50)
            ->withQueryString();

        $stats = [
            'total'     => Cotisation::where('saison_id', $saison?->id)->count(),
            'brouillon' => Cotisation::where('saison_id', $saison?->id)->where('statut', 'brouillon')->count(),
            'envoye'    => Cotisation::where('saison_id', $saison?->id)->where('statut', 'envoye')->count(),
            'paye'      => Cotisation::where('saison_id', $saison?->id)->where('statut', 'paye')->count(),
            'montant_encaisse' => Cotisation::where('saison_id', $saison?->id)->where('statut', 'paye')->sum('montant'),
        ];

        return Inertia::render('Cotisations/Index', [
            'cotisations' => $cotisations,
            'saison'      => $saison,
            'saisons'     => Saison::orderByDesc('annee_debut')->get(),
            'stats'       => $stats,
            'filters'     => $request->only(['search', 'statut', 'saison_id']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'saison_id'  => 'required|exists:saisons,id',
            'membre_id'  => 'required|exists:membres,id',
            'type'       => 'required|in:individuel,famille',
            'montant'    => 'required|numeric|min:0',
            'mode_envoi' => 'required|in:email,postal',
            'notes'      => 'nullable|string',
        ]);

        $cotisation = Cotisation::updateOrCreate(
            ['saison_id' => $data['saison_id'], 'membre_id' => $data['membre_id']],
            $data
        );

        return back()->with('success', 'Cotisation enregistrée.');
    }

    public function update(Request $request, Cotisation $cotisation): RedirectResponse
    {
        $data = $request->validate([
            'montant'    => 'required|numeric|min:0',
            'statut'     => 'required|in:brouillon,envoye,paye,annule',
            'mode_envoi' => 'required|in:email,postal',
            'notes'      => 'nullable|string',
        ]);

        $cotisation->update($data);

        return back()->with('success', 'Cotisation mise à jour.');
    }

    public function destroy(Cotisation $cotisation): RedirectResponse
    {
        $cotisation->delete();

        return back()->with('success', 'Cotisation supprimée.');
    }

    /** Envoie une cotisation par email ou marque comme "à imprimer" si postal */
    public function envoyer(Cotisation $cotisation): RedirectResponse
    {
        $cotisation->load('membre', 'saison');

        if ($cotisation->mode_envoi === 'email') {
            if (! $cotisation->membre->email) {
                return back()->withErrors(['email' => 'Ce membre n\'a pas d\'adresse email.']);
            }
            Mail::to($cotisation->membre->email)
                ->send(new CotisationMail($cotisation));
        }

        $cotisation->update([
            'statut'    => 'envoye',
            'envoye_le' => now(),
        ]);

        $action = $cotisation->mode_envoi === 'email' ? 'envoyée par email' : 'marquée pour envoi postal';
        return back()->with('success', "Cotisation {$action}.");
    }

    public function marquerPaye(Cotisation $cotisation): RedirectResponse
    {
        $cotisation->update([
            'statut'  => 'paye',
            'paye_le' => now(),
        ]);

        return back()->with('success', 'Cotisation marquée comme payée.');
    }

    /** Envoi en masse de toutes les cotisations "brouillon" */
    public function envoyerMasse(Request $request): RedirectResponse
    {
        $saison = Saison::findOrFail($request->saison_id);

        $cotisations = Cotisation::with('membre', 'saison')
            ->where('saison_id', $saison->id)
            ->where('statut', 'brouillon')
            ->get();

        $envoyes = 0;
        foreach ($cotisations as $cot) {
            if ($cot->mode_envoi === 'email' && $cot->membre->email) {
                Mail::to($cot->membre->email)->send(new CotisationMail($cot));
            }
            $cot->update(['statut' => 'envoye', 'envoye_le' => now()]);
            $envoyes++;
        }

        return back()->with('success', "{$envoyes} cotisation(s) envoyée(s).");
    }

    /** Génère un PDF de la cotisation */
    public function pdf(Cotisation $cotisation)
    {
        $cotisation->load('membre', 'saison');

        $pdf = Pdf::loadView('pdf.cotisation', compact('cotisation'));

        return $pdf->download("cotisation-{$cotisation->membre->nom}-{$cotisation->saison->libelle}.pdf");
    }
}
