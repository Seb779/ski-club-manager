<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Chrono extends Model
{
    protected $fillable = [
        'participant_id', 'manche', 'temps_ms', 'disqualifie', 'raison_dq',
    ];

    protected $casts = [
        'disqualifie' => 'boolean',
    ];

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function getTempsFormateAttribute(): ?string
    {
        return $this->temps_ms ? Course::formatTemps($this->temps_ms) : null;
    }
}
