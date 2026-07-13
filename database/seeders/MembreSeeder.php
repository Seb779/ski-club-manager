<?php

namespace Database\Seeders;

use App\Models\Membre;
use Illuminate\Database\Seeder;

class MembreSeeder extends Seeder
{
    public function run(): void
    {
        // ── Moniteurs (individuels) ────────────────────────────────────────────
        $sophie = Membre::create([
            'prenom' => 'Sophie', 'nom' => 'Roux',
            'email' => 'sophie.roux@skiclub.ch', 'telephone' => '079 111 22 33',
            'type' => 'individuel', 'moniteur' => true, 'actif' => true,
            'date_naissance' => '1985-03-12',
            'adresse' => 'Rue de la Station 4', 'npa' => '1950', 'localite' => 'Sion',
            'preference_envoi' => 'email',
        ]);

        $marc = Membre::create([
            'prenom' => 'Marc', 'nom' => 'Favre',
            'email' => 'marc.favre@skiclub.ch', 'telephone' => '079 222 33 44',
            'type' => 'individuel', 'moniteur' => true, 'actif' => true,
            'date_naissance' => '1978-07-22',
            'adresse' => 'Chemin des Alpes 12', 'npa' => '1950', 'localite' => 'Sion',
            'preference_envoi' => 'email',
        ]);

        Membre::create([
            'prenom' => 'Anne', 'nom' => 'Blanc',
            'email' => 'anne.blanc@email.ch',
            'type' => 'individuel', 'moniteur' => true, 'actif' => true,
            'date_naissance' => '1990-11-05',
            'preference_envoi' => 'email',
        ]);

        // ── Famille Dubois ─────────────────────────────────────────────────────
        $pierre = Membre::create([
            'prenom' => 'Pierre', 'nom' => 'Dubois',
            'email' => 'pierre.dubois@email.ch', 'telephone' => '027 300 10 20',
            'type' => 'chef_famille', 'actif' => true,
            'date_naissance' => '1975-06-14',
            'adresse' => 'Route de Champsec 8', 'npa' => '1950', 'localite' => 'Sion',
            'preference_envoi' => 'email',
        ]);

        Membre::create([
            'parent_id' => $pierre->id,
            'prenom' => 'Léa', 'nom' => 'Dubois',
            'type' => 'enfant', 'actif' => true,
            'date_naissance' => '2014-02-18',   // Minimes F
            'preference_envoi' => 'email',
        ]);

        Membre::create([
            'parent_id' => $pierre->id,
            'prenom' => 'Marc', 'nom' => 'Dubois',
            'type' => 'enfant', 'actif' => true,
            'date_naissance' => '2016-09-03',   // Benjamins G
            'preference_envoi' => 'email',
        ]);

        // ── Famille Martin ─────────────────────────────────────────────────────
        $claire = Membre::create([
            'prenom' => 'Claire', 'nom' => 'Martin',
            'email' => 'claire.martin@email.ch',
            'type' => 'chef_famille', 'actif' => true,
            'date_naissance' => '1980-04-20',
            'adresse' => 'Avenue de la Gare 15', 'npa' => '1950', 'localite' => 'Sion',
            'preference_envoi' => 'email',
        ]);

        Membre::create([
            'parent_id' => $claire->id,
            'prenom' => 'Tom', 'nom' => 'Martin',
            'type' => 'enfant', 'actif' => true,
            'date_naissance' => '2016-05-11',   // Benjamins G
            'preference_envoi' => 'email',
        ]);

        // ── Famille Rochat (envoi postal) ──────────────────────────────────────
        $jean = Membre::create([
            'prenom' => 'Jean-Pierre', 'nom' => 'Rochat',
            'email' => null,
            'type' => 'chef_famille', 'actif' => true,
            'date_naissance' => '1965-12-01',
            'adresse' => 'Hameau des Vignes 3', 'npa' => '1955', 'localite' => 'Saint-Pierre-de-Clages',
            'preference_envoi' => 'postal',  // courrier papier
        ]);

        Membre::create([
            'parent_id' => $jean->id,
            'prenom' => 'Anna', 'nom' => 'Rochat',
            'type' => 'enfant', 'actif' => true,
            'date_naissance' => '2008-03-25',   // Juniors F
            'preference_envoi' => 'postal',
        ]);

        Membre::create([
            'parent_id' => $jean->id,
            'prenom' => 'Lucas', 'nom' => 'Rochat',
            'type' => 'enfant', 'actif' => true,
            'date_naissance' => '2012-07-14',   // Cadets G
            'preference_envoi' => 'postal',
        ]);

        // ── Individuel Jean-Baptiste ───────────────────────────────────────────
        Membre::create([
            'prenom' => 'Jean-Baptiste', 'nom' => 'Favre',
            'email' => 'jb.favre@email.ch',
            'type' => 'individuel', 'actif' => true,
            'date_naissance' => '1995-08-30',
            'preference_envoi' => 'email',
        ]);

        // ── Quelques membres supplémentaires pour le concours ─────────────────
        $extras = [
            ['Louis', 'Bonvin',   '2012-04-10', 'enfant'],
            ['Kilian', 'Maret',   '2012-11-22', 'enfant'],
            ['Zoé',   'Fellay',   '2013-06-15', 'enfant'],
            ['Hugo',  'Germanier','2015-01-08', 'enfant'],
            ['Emma',  'Epiney',   '2014-09-20', 'enfant'],
        ];

        foreach ($extras as [$p, $n, $dob, $type]) {
            Membre::create([
                'prenom' => $p, 'nom' => $n,
                'type' => $type, 'actif' => true,
                'date_naissance' => $dob,
                'preference_envoi' => 'email',
            ]);
        }
    }
}
