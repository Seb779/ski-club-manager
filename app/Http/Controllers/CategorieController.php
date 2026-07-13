<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use App\Models\Saison;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CategorieController extends Controller
{
    public function index()
    {
        $saison = Saison::active();

        return inertia('Parametres/Categories', [
            'saison'     => $saison,
            'categories' => $saison?->categories()->orderBy('ordre')->get() ?? collect(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'saison_id'           => 'required|exists:saisons,id',
            'nom'                 => 'required|string|max:100',
            'annee_naissance_min' => 'required|integer|min:1950|max:2030',
            'annee_naissance_max' => 'required|integer|min:1950|max:2030|gte:annee_naissance_min',
            'genre'               => 'required|in:M,F,mixte',
            'ordre'               => 'integer|min:0',
        ]);

        Categorie::create($data);

        return back()->with('success', "Catégorie \"{$data['nom']}\" créée.");
    }

    public function update(Request $request, Categorie $categorie): RedirectResponse
    {
        $data = $request->validate([
            'nom'                 => 'required|string|max:100',
            'annee_naissance_min' => 'required|integer|min:1950|max:2030',
            'annee_naissance_max' => 'required|integer|min:1950|max:2030|gte:annee_naissance_min',
            'genre'               => 'required|in:M,F,mixte',
            'ordre'               => 'integer|min:0',
        ]);

        $categorie->update($data);

        return back()->with('success', 'Catégorie mise à jour.');
    }

    public function destroy(Categorie $categorie): RedirectResponse
    {
        $categorie->delete();

        return back()->with('success', 'Catégorie supprimée.');
    }
}
