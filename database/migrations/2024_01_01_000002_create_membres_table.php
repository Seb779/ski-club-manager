<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('membres')->nullOnDelete();
            $table->string('prenom');
            $table->string('nom');
            $table->date('date_naissance')->nullable();
            $table->string('email')->nullable();
            $table->string('telephone')->nullable();
            $table->text('adresse')->nullable();
            $table->string('npa', 10)->nullable();
            $table->string('localite')->nullable();
            $table->enum('type', ['individuel', 'chef_famille', 'enfant'])->default('individuel');
            $table->enum('preference_envoi', ['email', 'postal'])->default('email');
            $table->boolean('actif')->default(true);
            $table->boolean('moniteur')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membres');
    }
};
