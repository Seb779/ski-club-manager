<?php

namespace Database\Seeders;

use App\Models\Categorie;
use App\Models\Saison;
use Illuminate\Database\Seeder;

class SaisonSeeder extends Seeder
{
    public function run(): void
    {
        // Saison précédente (archivée)
        $precedente = Saison::create([
            'libelle'                  => '2024-2025',
            'annee_debut'              => 2024,
            'annee_fin'                => 2025,
            'active'                   => false,
            'archivee'                 => true,
            'cotisation_adulte'        => 75.00,
            'cotisation_enfant'        => 45.00,
            'cotisation_famille_base'  => 120.00,
            'cotisation_enfant_sup'    => 25.00,
            'iban'                     => 'CH56 0483 5012 3456 7800 9',
        ]);

        // Saison active
        $saison = Saison::create([
            'libelle'                  => '2025-2026',
            'annee_debut'              => 2025,
            'annee_fin'                => 2026,
            'active'                   => true,
            'archivee'                 => false,
            'cotisation_adulte'        => 80.00,
            'cotisation_enfant'        => 50.00,
            'cotisation_famille_base'  => 130.00,
            'cotisation_enfant_sup'    => 30.00,
            'iban'                     => 'CH56 0483 5012 3456 7800 9',
            'notes'                    => 'Saison hiver 2025-2026 — Concours interne prévu le 15 février 2026.',
        ]);

        // Catégories de course pour la saison active
        $categories = [
            ['nom' => 'Poussins',  'min' => 2017, 'max' => 2019, 'genre' => 'mixte', 'ordre' => 1],
            ['nom' => 'Benjamins', 'min' => 2015, 'max' => 2016, 'genre' => 'M',     'ordre' => 2],
            ['nom' => 'Benjamines','min' => 2015, 'max' => 2016, 'genre' => 'F',     'ordre' => 3],
            ['nom' => 'Minimes',   'min' => 2013, 'max' => 2014, 'genre' => 'M',     'ordre' => 4],
            ['nom' => 'Minimes',   'min' => 2013, 'max' => 2014, 'genre' => 'F',     'ordre' => 5],
            ['nom' => 'Cadets',    'min' => 2011, 'max' => 2012, 'genre' => 'M',     'ordre' => 6],
            ['nom' => 'Cadettes',  'min' => 2011, 'max' => 2012, 'genre' => 'F',     'ordre' => 7],
            ['nom' => 'Juniors',   'min' => 2007, 'max' => 2010, 'genre' => 'mixte', 'ordre' => 8],
        ];

        foreach ($categories as $cat) {
            Categorie::create([
                'saison_id'           => $saison->id,
                'nom'                 => $cat['nom'],
                'annee_naissance_min' => $cat['min'],
                'annee_naissance_max' => $cat['max'],
                'genre'               => $cat['genre'],
                'ordre'               => $cat['ordre'],
            ]);
        }
    }
}
