<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Catégories par années de naissance (configurables par saison)
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('saison_id')->constrained()->cascadeOnDelete();
            $table->string('nom');                           // "Benjamins", "Minimes", etc.
            $table->integer('annee_naissance_min');
            $table->integer('annee_naissance_max');
            $table->enum('genre', ['M', 'F', 'mixte'])->default('mixte');
            $table->integer('ordre')->default(0);
            $table->timestamps();
        });

        // Courses / concours
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('saison_id')->constrained()->cascadeOnDelete();
            $table->string('nom');
            $table->date('date')->nullable();
            $table->string('lieu')->nullable();
            $table->integer('nb_manches')->default(1);
            $table->enum('statut', ['preparation', 'actif', 'termine', 'archive'])->default('preparation');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Participants à une course (avec dossard)
        Schema::create('participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('membre_id')->constrained()->cascadeOnDelete();
            $table->foreignId('categorie_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->integer('dossard');
            $table->enum('statut', ['inscrit', 'disqualifie', 'forfait', 'classe'])->default('inscrit');
            $table->timestamps();
            $table->unique(['course_id', 'membre_id']);
            $table->unique(['course_id', 'dossard']);
        });

        // Chronos par manche
        Schema::create('chronos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')->constrained()->cascadeOnDelete();
            $table->integer('manche')->default(1);
            $table->integer('temps_ms')->nullable();  // stocké en millisecondes pour précision
            $table->boolean('disqualifie')->default(false);
            $table->string('raison_dq')->nullable();
            $table->timestamps();
            $table->unique(['participant_id', 'manche']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chronos');
        Schema::dropIfExists('participants');
        Schema::dropIfExists('courses');
        Schema::dropIfExists('categories');
    }
};
