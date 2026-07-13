<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cotisation extends Model
{
    protected $fillable = [
        'saison_id', 'membre_id', 'type', 'montant',
        'statut', 'mode_envoi', 'envoye_le', 'paye_le', 'reference', 'notes',
    ];

    protected $casts = [
        'montant'   => 'decimal:2',
        'envoye_le' => 'datetime',
        'paye_le'   => 'datetime',
    ];

    public function saison(): BelongsTo
    {
        return $this->belongsTo(Saison::class);
    }

    public function membre(): BelongsTo
    {
        return $this->belongsTo(Membre::class);
    }

    public function getStatutLabelAttribute(): string
    {
        return match($this->statut) {
            'brouillon' => 'Non envoyé',
            'envoye'    => 'Envoyé',
            'paye'      => 'Payé',
            'annule'    => 'Annulé',
            default     => $this->statut,
        };
    }

    public function getStatutColorAttribute(): string
    {
        return match($this->statut) {
            'brouillon' => 'gray',
            'envoye'    => 'yellow',
            'paye'      => 'green',
            'annule'    => 'red',
            default     => 'gray',
        };
    }

    /** Calcule le montant selon le type et la saison */
    public static function calculerMontant(Saison $saison, Membre $membre): float
    {
        if ($membre->type === 'chef_famille') {
            $nbEnfants = $membre->enfants()->count();
            return (float) $saison->cotisation_famille_base
                + ($nbEnfants * (float) $saison->cotisation_enfant_sup);
        }

        if ($membre->annee_naissance && $membre->annee_naissance >= (date('Y') - 17)) {
            return (float) $saison->cotisation_enfant;
        }

        return (float) $saison->cotisation_adulte;
    }
}
