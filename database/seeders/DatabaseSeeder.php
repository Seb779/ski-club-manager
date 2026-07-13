<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SaisonSeeder::class,
            MembreSeeder::class,
            GroupeSeeder::class,
            CotisationSeeder::class,
            CourseSeeder::class,
            CourrierSeeder::class,
        ]);
    }
}
