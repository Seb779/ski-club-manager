<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Groupe extends Model
{
    protected $fillable = [
        'saison_id', 'moniteur_id', 'nom', 'couleur', 'description', 'ordre',
    ];

    public function saison(): BelongsTo
    {
        return $this->belongsTo(Saison::class);
    }

    public function moniteur(): BelongsTo
    {
        return $this->belongsTo(Membre::class, 'moniteur_id');
    }

    public function membres(): BelongsToMany
    {
        return $this->belongsToMany(Membre::class, 'groupe_membre');
    }
}
