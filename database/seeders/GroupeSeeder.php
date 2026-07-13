<?php

namespace Database\Seeders;

use App\Models\Groupe;
use App\Models\Membre;
use App\Models\Saison;
use Illuminate\Database\Seeder;

class GroupeSeeder extends Seeder
{
    public function run(): void
    {
        $saison  = Saison::where('active', true)->first();
        $sophie  = Membre::where('nom', 'Roux')->first();
        $marc    = Membre::where('nom', 'Favre')->where('prenom', 'Marc')->first();
        $anne    = Membre::where('nom', 'Blanc')->first();

        $plaisir = Groupe::create([
            'saison_id'   => $saison->id,
            'moniteur_id' => $sophie->id,
            'nom'         => 'Ski-plaisir',
            'couleur'     => '#3b5bdb',
            'description' => 'Cours de ski pour les débutants et intermédiaires',
            'ordre'       => 1,
        ]);

        $competition = Groupe::create([
            'saison_id'   => $saison->id,
            'moniteur_id' => $marc->id,
            'nom'         => 'Ski-compétition',
            'couleur'     => '#e67700',
            'description' => 'Entraînement compétition et portes',
            'ordre'       => 2,
        ]);

        $debutants = Groupe::create([
            'saison_id'   => $saison->id,
            'moniteur_id' => $anne->id,
            'nom'         => 'Débutants',
            'couleur'     => '#2f9e44',
            'description' => 'Premiers pas sur les skis',
            'ordre'       => 3,
        ]);

        // Affecter les enfants aux groupes
        $lea   = Membre::where('prenom', 'Léa')->where('nom', 'Dubois')->first();
        $marcD = Membre::where('prenom', 'Marc')->where('nom', 'Dubois')->first();
        $tom   = Membre::where('prenom', 'Tom')->where('nom', 'Martin')->first();
        $anna  = Membre::where('prenom', 'Anna')->where('nom', 'Rochat')->first();
        $lucas = Membre::where('prenom', 'Lucas')->where('nom', 'Rochat')->first();

        $competition->membres()->attach([$lea->id, $anna->id]);
        $plaisir->membres()->attach([$marcD->id, $tom->id, $lucas->id]);
        $debutants->membres()->attach([
            Membre::where('prenom', 'Hugo')->first()?->id,
            Membre::where('prenom', 'Emma')->first()?->id,
        ]);
    }
}
