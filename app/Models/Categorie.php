<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categorie extends Model
{
    protected $fillable = [
        'saison_id', 'nom', 'annee_naissance_min', 'annee_naissance_max', 'genre', 'ordre',
    ];

    public function saison(): BelongsTo
    {
        return $this->belongsTo(Saison::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class);
    }

    public function getLibelleAttribute(): string
    {
        $genre = match($this->genre) {
            'M' => 'Garçons',
            'F' => 'Filles',
            default => 'Mixte',
        };
        return "{$this->nom} ({$this->annee_naissance_min}–{$this->annee_naissance_max}) · {$genre}";
    }
}
