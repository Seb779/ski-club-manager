<?php

namespace App\Http\Controllers;

use App\Models\Groupe;
use App\Models\Membre;
use App\Models\Saison;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GroupeController extends Controller
{
    public function index(): Response
    {
        $saison = Saison::active();

        $groupes = Groupe::with(['moniteur', 'membres'])
            ->when($saison, fn($q) => $q->where('saison_id', $saison->id))
            ->orderBy('ordre')
            ->get();

        return Inertia::render('Groupes/Index', [
            'groupes' => $groupes,
            'saison'  => $saison,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Groupes/Form', [
            'groupe'    => null,
            'saisons'   => Saison::orderByDesc('annee_debut')->get(),
            'moniteurs' => Membre::where('moniteur', true)->orderBy('nom')->get(['id', 'prenom', 'nom']),
            'mode'      => 'create',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'saison_id'   => 'required|exists:saisons,id',
            'moniteur_id' => 'nullable|exists:membres,id',
            'nom'         => 'required|string|max:150',
            'couleur'     => 'nullable|string|max:7',
            'description' => 'nullable|string',
            'ordre'       => 'integer|min:0',
        ]);

        Groupe::create($data);

        return redirect()->route('groupes.index')
            ->with('success', "Groupe \"{$data['nom']}\" créé.");
    }

    public function edit(Groupe $groupe): Response
    {
        $groupe->load('membres');

        return Inertia::render('Groupes/Form', [
            'groupe'    => $groupe,
            'saisons'   => Saison::orderByDesc('annee_debut')->get(),
            'moniteurs' => Membre::where('moniteur', true)->orderBy('nom')->get(['id', 'prenom', 'nom']),
            'mode'      => 'edit',
        ]);
    }

    public function update(Request $request, Groupe $groupe): RedirectResponse
    {
        $data = $request->validate([
            'moniteur_id' => 'nullable|exists:membres,id',
            'nom'         => 'required|string|max:150',
            'couleur'     => 'nullable|string|max:7',
            'description' => 'nullable|string',
            'ordre'       => 'integer|min:0',
        ]);

        $groupe->update($data);

        return back()->with('success', 'Groupe mis à jour.');
    }

    public function destroy(Groupe $groupe): RedirectResponse
    {
        $nom = $groupe->nom;
        $groupe->delete();

        return redirect()->route('groupes.index')
            ->with('success', "Groupe \"{$nom}\" supprimé.");
    }

    public function ajouterMembre(Groupe $groupe, Membre $membre): RedirectResponse
    {
        $groupe->membres()->syncWithoutDetaching([$membre->id]);

        return back()->with('success', "{$membre->nom_complet} ajouté au groupe.");
    }

    public function retirerMembre(Groupe $groupe, Membre $membre): RedirectResponse
    {
        $groupe->membres()->detach($membre->id);

        return back()->with('success', "{$membre->nom_complet} retiré du groupe.");
    }
}
