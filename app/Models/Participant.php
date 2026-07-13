<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Participant extends Model
{
    protected $fillable = [
        'course_id', 'membre_id', 'categorie_id', 'dossard', 'statut',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function membre(): BelongsTo
    {
        return $this->belongsTo(Membre::class);
    }

    public function categorie(): BelongsTo
    {
        return $this->belongsTo(Categorie::class);
    }

    public function chronos(): HasMany
    {
        return $this->hasMany(Chrono::class);
    }

    public function getTempsFormateManche(int $manche): ?string
    {
        $chrono = $this->chronos()->where('manche', $manche)->first();
        if (! $chrono || ! $chrono->temps_ms) {
            return null;
        }
        return Course::formatTemps($chrono->temps_ms);
    }
}
