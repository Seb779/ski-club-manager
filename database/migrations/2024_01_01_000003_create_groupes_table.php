<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('groupes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('saison_id')->constrained()->cascadeOnDelete();
            $table->foreignId('moniteur_id')->nullable()->constrained('membres')->nullOnDelete();
            $table->string('nom');                // "Ski-plaisir", "Ski-compétition", etc.
            $table->string('couleur', 7)->default('#3b5bdb'); // hex color
            $table->text('description')->nullable();
            $table->integer('ordre')->default(0);
            $table->timestamps();
        });

        Schema::create('groupe_membre', function (Blueprint $table) {
            $table->id();
            $table->foreignId('groupe_id')->constrained()->cascadeOnDelete();
            $table->foreignId('membre_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['groupe_id', 'membre_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('groupe_membre');
        Schema::dropIfExists('groupes');
    }
};
