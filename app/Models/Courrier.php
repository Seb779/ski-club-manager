<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Courrier extends Model
{
    protected $fillable = [
        'saison_id', 'titre', 'corps', 'statut', 'envoye_le', 'expediteur', 'notes',
    ];

    protected $casts = [
        'envoye_le' => 'datetime',
    ];

    public function saison(): BelongsTo
    {
        return $this->belongsTo(Saison::class);
    }

    public function membres(): BelongsToMany
    {
        return $this->belongsToMany(Membre::class, 'courrier_membre')
            ->withPivot(['mode_envoi', 'statut', 'traite_le', 'erreur_message'])
            ->withTimestamps();
    }

    public function getStatsEnvoiAttribute(): array
    {
        $pivot   = $this->membres;
        $total   = $pivot->count();
        $envoyes = $pivot->where('pivot.statut', 'envoye')->count();
        $erreurs = $pivot->where('pivot.statut', 'erreur')->count();
        $postaux = $pivot->where('pivot.mode_envoi', 'postal')->count();

        return compact('total', 'envoyes', 'erreurs', 'postaux');
    }
}
