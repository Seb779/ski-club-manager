<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courriers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('saison_id')->nullable()->constrained()->nullOnDelete();
            $table->string('titre');
            $table->longText('corps');               // HTML (éditeur WYSIWYG)
            $table->enum('statut', ['brouillon', 'envoye'])->default('brouillon');
            $table->timestamp('envoye_le')->nullable();
            $table->string('expediteur')->nullable(); // override de l'adresse email
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Destinataires d'un courrier (un enregistrement par membre)
        Schema::create('courrier_membre', function (Blueprint $table) {
            $table->id();
            $table->foreignId('courrier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('membre_id')->constrained()->cascadeOnDelete();
            $table->enum('mode_envoi', ['email', 'postal'])->default('email');
            $table->enum('statut', ['en_attente', 'envoye', 'erreur', 'imprime'])->default('en_attente');
            $table->timestamp('traite_le')->nullable();
            $table->string('erreur_message')->nullable();
            $table->timestamps();
            $table->unique(['courrier_id', 'membre_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courrier_membre');
        Schema::dropIfExists('courriers');
    }
};
