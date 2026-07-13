<?php

namespace App\Http\Controllers;

use App\Models\Membre;
use App\Models\Saison;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MembreController extends Controller
{
    public function index(Request $request): Response
    {
        $saison = Saison::active();

        $membres = Membre::query()
            ->with(['parent', 'enfants', 'groupes' => fn($q) => $q->where('saison_id', $saison?->id)])
            ->when($request->search, fn($q, $s) =>
                $q->where(fn($q) => $q
                    ->where('prenom', 'like', "%{$s}%")
                    ->orWhere('nom', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
                )
            )
            ->when($request->type, fn($q, $t) => $q->where('type', $t))
            ->when($request->actif !== null, fn($q) => $q->where('actif', $request->actif))
            ->orderBy('nom')->orderBy('prenom')
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('Membres/Index', [
            'membres' => $membres,
            'saison'  => $saison,
            'filters' => $request->only(['search', 'type', 'actif']),
        ]);
    }

    public function create(): Response
    {
        $membres = Membre::select('id', 'prenom', 'nom', 'type')
            ->whereIn('type', ['chef_famille', 'individuel'])
            ->orderBy('nom')->get();

        return Inertia::render('Membres/Form', [
            'membre'    => null,
            'parents'   => $membres,
            'mode'      => 'create',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'parent_id'       => 'nullable|exists:membres,id',
            'prenom'          => 'required|string|max:100',
            'nom'             => 'required|string|max:100',
            'date_naissance'  => 'nullable|date',
            'email'           => 'nullable|email|max:200',
            'telephone'       => 'nullable|string|max:50',
            'adresse'         => 'nullable|string|max:255',
            'npa'             => 'nullable|string|max:10',
            'localite'        => 'nullable|string|max:100',
            'type'            => 'required|in:individuel,chef_famille,enfant',
            'preference_envoi'=> 'required|in:email,postal',
            'moniteur'        => 'boolean',
            'notes'           => 'nullable|string',
        ]);

        $membre = Membre::create($data);

        return redirect()->route('membres.show', $membre)
            ->with('success', "Membre {$membre->nom_complet} créé avec succès.");
    }

    public function show(Membre $membre): Response
    {
        $saison = Saison::active();

        $membre->load([
            'parent',
            'enfants.groupes',
            'groupes',
            'cotisations.saison',
            'participants.course',
            'participants.categorie',
            'participants.chronos',
        ]);

        return Inertia::render('Membres/Show', [
            'membre'     => $membre,
            'saison'     => $saison,
            'cotisation' => $saison ? $membre->cotisationPourSaison($saison->id) : null,
        ]);
    }

    public function edit(Membre $membre): Response
    {
        $parents = Membre::select('id', 'prenom', 'nom', 'type')
            ->whereIn('type', ['chef_famille', 'individuel'])
            ->where('id', '!=', $membre->id)
            ->orderBy('nom')->get();

        return Inertia::render('Membres/Form', [
            'membre'  => $membre->load('enfants'),
            'parents' => $parents,
            'mode'    => 'edit',
        ]);
    }

    public function update(Request $request, Membre $membre): RedirectResponse
    {
        $data = $request->validate([
            'parent_id'       => 'nullable|exists:membres,id',
            'prenom'          => 'required|string|max:100',
            'nom'             => 'required|string|max:100',
            'date_naissance'  => 'nullable|date',
            'email'           => 'nullable|email|max:200',
            'telephone'       => 'nullable|string|max:50',
            'adresse'         => 'nullable|string|max:255',
            'npa'             => 'nullable|string|max:10',
            'localite'        => 'nullable|string|max:100',
            'type'            => 'required|in:individuel,chef_famille,enfant',
            'preference_envoi'=> 'required|in:email,postal',
            'actif'           => 'boolean',
            'moniteur'        => 'boolean',
            'notes'           => 'nullable|string',
        ]);

        $membre->update($data);

        return redirect()->route('membres.show', $membre)
            ->with('success', 'Membre mis à jour.');
    }

    public function destroy(Membre $membre): RedirectResponse
    {
        $nom = $membre->nom_complet;
        $membre->delete(); // soft delete

        return redirect()->route('membres.index')
            ->with('success', "{$nom} supprimé.");
    }

    public function famille(Membre $membre): Response
    {
        $membre->load(['parent', 'enfants.groupes', 'enfants.cotisations']);

        return Inertia::render('Membres/Famille', [
            'membre' => $membre,
            'chef'   => $membre->type === 'chef_famille' ? $membre : $membre->parent,
        ]);
    }
}
