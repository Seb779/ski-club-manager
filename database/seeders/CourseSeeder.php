<?php

namespace Database\Seeders;

use App\Models\Categorie;
use App\Models\Chrono;
use App\Models\Course;
use App\Models\Membre;
use App\Models\Participant;
use App\Models\Saison;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $saison = Saison::where('active', true)->first();

        $course = Course::create([
            'saison_id'  => $saison->id,
            'nom'        => 'Concours interne 2026',
            'date'       => '2026-02-15',
            'lieu'       => 'Piste des Chamois — Télésiège du haut',
            'nb_manches' => 2,
            'statut'     => 'actif',
            'notes'      => 'Slalom parallèle. Bib remis à l\'inscription. Résultats sur la somme des 2 manches.',
        ]);

        // Participants avec leurs catégories
        $inscriptions = [
            ['prenom' => 'Léa',         'nom' => 'Dubois',    'dossard' => 1,  'chrono1' => '1:24.83', 'chrono2' => '1:22.40'],
            ['prenom' => 'Tom',         'nom' => 'Martin',    'dossard' => 2,  'chrono1' => '1:31.22', 'chrono2' => null],
            ['prenom' => 'Lucas',       'nom' => 'Rochat',    'dossard' => 3,  'chrono1' => '1:19.21', 'chrono2' => '1:17.85'],
            ['prenom' => 'Anna',        'nom' => 'Rochat',    'dossard' => 4,  'chrono1' => '1:28.54', 'chrono2' => '1:27.10'],
            ['prenom' => 'Louis',       'nom' => 'Bonvin',    'dossard' => 5,  'chrono1' => '1:21.54', 'chrono2' => '1:20.30'],
            ['prenom' => 'Kilian',      'nom' => 'Maret',     'dossard' => 6,  'chrono1' => '1:22.07', 'chrono2' => null],
            ['prenom' => 'Zoé',         'nom' => 'Fellay',    'dossard' => 7,  'chrono1' => '1:35.60', 'chrono2' => '1:33.20'],
            ['prenom' => 'Hugo',        'nom' => 'Germanier', 'dossard' => 8,  'chrono1' => '1:45.00', 'chrono2' => '1:43.50'],
            ['prenom' => 'Emma',        'nom' => 'Epiney',    'dossard' => 9,  'chrono1' => null,       'chrono2' => null],
            ['prenom' => 'Marc',        'nom' => 'Dubois',    'dossard' => 10, 'chrono1' => '1:38.45', 'chrono2' => '1:36.80'],
        ];

        foreach ($inscriptions as $ins) {
            $membre = Membre::where('prenom', $ins['prenom'])->where('nom', $ins['nom'])->first();
            if (! $membre) continue;

            $categorie = $membre->getCategorieForSaison($saison);

            $participant = Participant::create([
                'course_id'    => $course->id,
                'membre_id'    => $membre->id,
                'categorie_id' => $categorie?->id,
                'dossard'      => $ins['dossard'],
                'statut'       => ($ins['chrono1'] || $ins['chrono2']) ? 'classe' : 'inscrit',
            ]);

            if ($ins['chrono1']) {
                Chrono::create([
                    'participant_id' => $participant->id,
                    'manche'         => 1,
                    'temps_ms'       => Course::parseTemps($ins['chrono1']),
                ]);
            }
            if ($ins['chrono2']) {
                Chrono::create([
                    'participant_id' => $participant->id,
                    'manche'         => 2,
                    'temps_ms'       => Course::parseTemps($ins['chrono2']),
                ]);
            }
        }
    }
}
