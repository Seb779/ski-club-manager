<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    protected $fillable = [
        'saison_id', 'nom', 'date', 'lieu', 'nb_manches', 'statut', 'notes',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function saison(): BelongsTo
    {
        return $this->belongsTo(Saison::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class);
    }

    /** Classement par catégorie pour une manche donnée (ou total si nb_manches > 1) */
    public function classement(?int $categorieId = null, int $manche = 0): \Illuminate\Support\Collection
    {
        $query = $this->participants()
            ->with(['membre', 'categorie', 'chronos'])
            ->where('statut', 'classe');

        if ($categorieId) {
            $query->where('categorie_id', $categorieId);
        }

        $participants = $query->get();

        return $participants->map(function ($p) use ($manche) {
            if ($manche > 0) {
                $chrono = $p->chronos->where('manche', $manche)->where('disqualifie', false)->first();
                $temps  = $chrono?->temps_ms;
            } else {
                // Somme toutes les manches non-DQ
                $temps = $p->chronos->where('disqualifie', false)->sum('temps_ms') ?: null;
            }

            return [
                'participant' => $p,
                'temps_ms'    => $temps,
                'temps_formate' => $temps ? self::formatTemps($temps) : null,
                'dq'          => $p->chronos->where('disqualifie', true)->isNotEmpty(),
            ];
        })
        ->filter(fn($r) => ! $r['dq'] && $r['temps_ms'] !== null)
        ->sortBy('temps_ms')
        ->values();
    }

    public static function formatTemps(int $ms): string
    {
        $minutes = intdiv($ms, 60000);
        $seconds = intdiv($ms % 60000, 1000);
        $centis  = intdiv(($ms % 1000), 10);

        return sprintf('%d:%02d.%02d', $minutes, $seconds, $centis);
    }

    /** Parse "1:23.45" → millisecondes */
    public static function parseTemps(string $texte): int
    {
        if (preg_match('/^(\d+):(\d{2})\.(\d{2})$/', trim($texte), $m)) {
            return ($m[1] * 60000) + ($m[2] * 1000) + ($m[3] * 10);
        }
        if (preg_match('/^(\d+)\.(\d{2})$/', trim($texte), $m)) {
            return ($m[1] * 1000) + ($m[2] * 10);
        }
        throw new \InvalidArgumentException("Format invalide: {$texte}. Attendu: 1:23.45 ou 23.45");
    }
}
