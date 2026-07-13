<?php

namespace App\Http\Controllers;

use App\Mail\CourrierMail;
use App\Models\Courrier;
use App\Models\Membre;
use App\Models\Saison;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class CourrierController extends Controller
{
    public function index(): Response
    {
        $courriers = Courrier::with(['saison'])
            ->withCount('membres')
            ->orderByDesc('updated_at')
            ->paginate(30);

        return Inertia::render('Courriers/Index', [
            'courriers' => $courriers,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Courriers/Form', [
            'courrier' => null,
            'saisons'  => Saison::orderByDesc('annee_debut')->get(),
            'membres'  => Membre::actifs()->orderBy('nom')->get(['id', 'prenom', 'nom', 'email', 'preference_envoi']),
            'mode'     => 'create',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'saison_id'    => 'nullable|exists:saisons,id',
            'titre'        => 'required|string|max:255',
            'corps'        => 'required|string',
            'expediteur'   => 'nullable|email',
            'notes'        => 'nullable|string',
            'membres'      => 'array',
            'membres.*'    => 'exists:membres,id',
        ]);

        $courrier = Courrier::create($data);

        // Attacher les destinataires
        if (! empty($data['membres'])) {
            $pivot = [];
            foreach ($data['membres'] as $membreId) {
                $m = Membre::find($membreId);
                $pivot[$membreId] = ['mode_envoi' => $m->preference_envoi, 'statut' => 'en_attente'];
            }
            $courrier->membres()->attach($pivot);
        }

        return redirect()->route('courriers.show', $courrier)
            ->with('success', 'Courrier créé.');
    }

    public function show(Courrier $courrier): Response
    {
        $courrier->load(['saison', 'membres']);

        return Inertia::render('Courriers/Show', [
            'courrier' => $courrier,
            'stats'    => $courrier->stats_envoi,
        ]);
    }

    public function edit(Courrier $courrier): Response
    {
        $courrier->load('membres');

        return Inertia::render('Courriers/Form', [
            'courrier'        => $courrier,
            'saisons'         => Saison::orderByDesc('annee_debut')->get(),
            'membres'         => Membre::actifs()->orderBy('nom')->get(['id', 'prenom', 'nom', 'email', 'preference_envoi']),
            'membres_selects' => $courrier->membres->pluck('id'),
            'mode'            => 'edit',
        ]);
    }

    public function update(Request $request, Courrier $courrier): RedirectResponse
    {
        $data = $request->validate([
            'saison_id'  => 'nullable|exists:saisons,id',
            'titre'      => 'required|string|max:255',
            'corps'      => 'required|string',
            'expediteur' => 'nullable|email',
            'notes'      => 'nullable|string',
            'membres'    => 'array',
            'membres.*'  => 'exists:membres,id',
        ]);

        $courrier->update($data);

        if (isset($data['membres'])) {
            $pivot = [];
            foreach ($data['membres'] as $membreId) {
                $m = Membre::find($membreId);
                $existing = $courrier->membres()->where('membre_id', $membreId)->first();
                $pivot[$membreId] = [
                    'mode_envoi' => $existing?->pivot->mode_envoi ?? $m->preference_envoi,
                    'statut'     => $existing?->pivot->statut ?? 'en_attente',
                ];
            }
            $courrier->membres()->sync($pivot);
        }

        return redirect()->route('courriers.show', $courrier)
            ->with('success', 'Courrier mis à jour.');
    }

    public function destroy(Courrier $courrier): RedirectResponse
    {
        $courrier->delete();

        return redirect()->route('courriers.index')
            ->with('success', 'Courrier supprimé.');
    }

    /** Envoie le courrier à tous les destinataires */
    public function envoyer(Courrier $courrier): RedirectResponse
    {
        if ($courrier->statut === 'envoye') {
            return back()->withErrors(['statut' => 'Ce courrier a déjà été envoyé.']);
        }

        $courrier->load('membres');
        $envoyes = 0;
        $erreurs = 0;

        foreach ($courrier->membres as $membre) {
            try {
                if ($membre->pivot->mode_envoi === 'email') {
                    if (! $membre->email) {
                        $courrier->membres()->updateExistingPivot($membre->id, [
                            'statut'          => 'erreur',
                            'erreur_message'  => 'Pas d\'adresse email',
                            'traite_le'       => now(),
                        ]);
                        $erreurs++;
                        continue;
                    }
                    Mail::to($membre->email)->send(new CourrierMail($courrier, $membre));
                    $courrier->membres()->updateExistingPivot($membre->id, [
                        'statut'     => 'envoye',
                        'traite_le'  => now(),
                    ]);
                } else {
                    // Postal : marquer "imprimé" — le PDF sera généré séparément
                    $courrier->membres()->updateExistingPivot($membre->id, [
                        'statut'    => 'imprime',
                        'traite_le' => now(),
                    ]);
                }
                $envoyes++;
            } catch (\Exception $e) {
                $courrier->membres()->updateExistingPivot($membre->id, [
                    'statut'         => 'erreur',
                    'erreur_message' => $e->getMessage(),
                    'traite_le'      => now(),
                ]);
                $erreurs++;
            }
        }

        $courrier->update(['statut' => 'envoye', 'envoye_le' => now()]);

        $msg = "{$envoyes} envoi(s) traité(s)";
        if ($erreurs) {
            $msg .= ", {$erreurs} erreur(s).";
        }

        return back()->with('success', $msg);
    }

    public function apercu(Courrier $courrier): Response
    {
        return Inertia::render('Courriers/Apercu', [
            'courrier' => $courrier,
        ]);
    }

    public function pdf(Courrier $courrier)
    {
        $pdf = Pdf::loadView('pdf.courrier', compact('courrier'));

        return $pdf->download("courrier-{$courrier->id}.pdf");
    }
}
