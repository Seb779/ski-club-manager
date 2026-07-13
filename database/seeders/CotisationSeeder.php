<?php

namespace Database\Seeders;

use App\Models\Cotisation;
use App\Models\Membre;
use App\Models\Saison;
use Illuminate\Database\Seeder;

class CotisationSeeder extends Seeder
{
    public function run(): void
    {
        $saison = Saison::where('active', true)->first();

        // Famille Dubois — cotisation famille PAYÉE
        $pierre = Membre::where('prenom', 'Pierre')->where('nom', 'Dubois')->first();
        Cotisation::create([
            'saison_id'  => $saison->id,
            'membre_id'  => $pierre->id,
            'type'       => 'famille',
            'montant'    => 130 + 30,   // base + 1 enfant sup (2 enfants → 1 inclus + 1 sup)
            'statut'     => 'paye',
            'mode_envoi' => 'email',
            'envoye_le'  => now()->subMonths(2),
            'paye_le'    => now()->subMonth(),
            'reference'  => 'DUB-2526-001',
        ]);

        // Famille Martin — cotisation famille ENVOYÉE (non payée)
        $claire = Membre::where('prenom', 'Claire')->where('nom', 'Martin')->first();
        Cotisation::create([
            'saison_id'  => $saison->id,
            'membre_id'  => $claire->id,
            'type'       => 'famille',
            'montant'    => 130.00,
            'statut'     => 'envoye',
            'mode_envoi' => 'email',
            'envoye_le'  => now()->subWeeks(3),
        ]);

        // Famille Rochat — cotisation postale ENVOYÉE
        $jean = Membre::where('prenom', 'Jean-Pierre')->where('nom', 'Rochat')->first();
        Cotisation::create([
            'saison_id'  => $saison->id,
            'membre_id'  => $jean->id,
            'type'       => 'famille',
            'montant'    => 130 + 30,
            'statut'     => 'envoye',
            'mode_envoi' => 'postal',
            'envoye_le'  => now()->subMonth(),
            'notes'      => 'BVR envoyé par courrier A',
        ]);

        // Moniteurs individuels — PAYÉS
        foreach (['Roux', 'Blanc'] as $nom) {
            $m = Membre::where('nom', $nom)->first();
            Cotisation::create([
                'saison_id'  => $saison->id,
                'membre_id'  => $m->id,
                'type'       => 'individuel',
                'montant'    => 80.00,
                'statut'     => 'paye',
                'mode_envoi' => 'email',
                'envoye_le'  => now()->subMonths(2),
                'paye_le'    => now()->subMonths(2)->addDays(5),
            ]);
        }

        // Marc Favre moniteur — BROUILLON (oublié)
        $marcF = Membre::where('prenom', 'Marc')->where('nom', 'Favre')->first();
        Cotisation::create([
            'saison_id'  => $saison->id,
            'membre_id'  => $marcF->id,
            'type'       => 'individuel',
            'montant'    => 80.00,
            'statut'     => 'brouillon',
            'mode_envoi' => 'email',
        ]);

        // JB Favre individuel — BROUILLON
        $jb = Membre::where('prenom', 'Jean-Baptiste')->first();
        Cotisation::create([
            'saison_id'  => $saison->id,
            'membre_id'  => $jb->id,
            'type'       => 'individuel',
            'montant'    => 80.00,
            'statut'     => 'brouillon',
            'mode_envoi' => 'email',
        ]);
    }
}
