<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use App\Models\Saison;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SaisonController extends Controller
{
    public function index(): Response
    {
        $saisons = Saison::withCount(['cotisations', 'groupes', 'courses'])
            ->orderByDesc('annee_debut')
            ->get();

        return Inertia::render('Parametres/Saisons', [
            'saisons' => $saisons,
        ]);
    }

    public function create(): Response
    {
        $derniere = Saison::orderByDesc('annee_debut')->first();
        $debut    = $derniere ? $derniere->annee_debut + 1 : (int) date('Y');

        return Inertia::render('Parametres/SaisonForm', [
            'saison'     => null,
            'annee_debut'=> $debut,
            'mode'       => 'create',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'annee_debut'             => 'required|integer|min:2020',
            'cotisation_adulte'       => 'required|numeric|min:0',
            'cotisation_enfant'       => 'required|numeric|min:0',
            'cotisation_famille_base' => 'required|numeric|min:0',
            'cotisation_enfant_sup'   => 'required|numeric|min:0',
            'iban'                    => 'nullable|string',
            'notes'                   => 'nullable|string',
        ]);

        $data['annee_fin'] = $data['annee_debut'] + 1;
        $data['libelle']   = "{$data['annee_debut']}-{$data['annee_fin']}";

        $saison = Saison::create($data);

        // Copier les catégories de la saison précédente
        $precedente = Saison::where('annee_debut', $data['annee_debut'] - 1)->first();
        if ($precedente) {
            foreach ($precedente->categories as $cat) {
                $saison->categories()->create([
                    'nom'                  => $cat->nom,
                    'annee_naissance_min'  => $cat->annee_naissance_min,
                    'annee_naissance_max'  => $cat->annee_naissance_max,
                    'genre'                => $cat->genre,
                    'ordre'                => $cat->ordre,
                ]);
            }
        }

        return redirect()->route('saisons.index')
            ->with('success', "Saison {$saison->libelle} créée.");
    }

    public function edit(Saison $saison): Response
    {
        $saison->load('categories');

        return Inertia::render('Parametres/SaisonForm', [
            'saison' => $saison,
            'mode'   => 'edit',
        ]);
    }

    public function update(Request $request, Saison $saison): RedirectResponse
    {
        $data = $request->validate([
            'cotisation_adulte'       => 'required|numeric|min:0',
            'cotisation_enfant'       => 'required|numeric|min:0',
            'cotisation_famille_base' => 'required|numeric|min:0',
            'cotisation_enfant_sup'   => 'required|numeric|min:0',
            'iban'                    => 'nullable|string',
            'notes'                   => 'nullable|string',
        ]);

        $saison->update($data);

        return redirect()->route('saisons.index')
            ->with('success', "Saison {$saison->libelle} mise à jour.");
    }

    public function destroy(Saison $saison): RedirectResponse
    {
        if ($saison->active) {
            return back()->withErrors(['active' => 'Impossible de supprimer la saison active.']);
        }
        $saison->delete();

        return redirect()->route('saisons.index')
            ->with('success', 'Saison supprimée.');
    }

    public function activer(Saison $saison): RedirectResponse
    {
        Saison::where('active', true)->update(['active' => false]);
        $saison->update(['active' => true, 'archivee' => false]);

        return back()->with('success', "Saison {$saison->libelle} activée.");
    }

    public function archiver(Saison $saison): RedirectResponse
    {
        if ($saison->active) {
            return back()->withErrors(['active' => 'Désactiver la saison avant de l\'archiver.']);
        }
        $saison->update(['archivee' => true]);

        return back()->with('success', "Saison {$saison->libelle} archivée.");
    }
}
