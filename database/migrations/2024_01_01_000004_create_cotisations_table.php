<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cotisations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('saison_id')->constrained()->cascadeOnDelete();
            $table->foreignId('membre_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['individuel', 'famille'])->default('individuel');
            $table->decimal('montant', 8, 2);
            $table->enum('statut', ['brouillon', 'envoye', 'paye', 'annule'])->default('brouillon');
            $table->enum('mode_envoi', ['email', 'postal'])->default('email');
            $table->timestamp('envoye_le')->nullable();
            $table->timestamp('paye_le')->nullable();
            $table->string('reference')->nullable();  // numéro de référence de paiement
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['saison_id', 'membre_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cotisations');
    }
};
