<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saisons', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');           // ex: "2025-2026"
            $table->integer('annee_debut');       // 2025
            $table->integer('annee_fin');         // 2026
            $table->boolean('active')->default(false);
            $table->boolean('archivee')->default(false);
            $table->decimal('cotisation_adulte', 8, 2)->default(80.00);
            $table->decimal('cotisation_enfant', 8, 2)->default(50.00);
            $table->decimal('cotisation_famille_base', 8, 2)->default(130.00);
            $table->decimal('cotisation_enfant_sup', 8, 2)->default(30.00);
            $table->text('iban')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saisons');
    }
};
