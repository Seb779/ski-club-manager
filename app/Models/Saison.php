<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Saison extends Model
{
    protected $fillable = [
        'libelle', 'annee_debut', 'annee_fin', 'active', 'archivee',
        'cotisation_adulte', 'cotisation_enfant',
        'cotisation_famille_base', 'cotisation_enfant_sup',
        'iban', 'notes',
    ];

    protected $casts = [
        'active'   => 'boolean',
        'archivee' => 'boolean',
        'cotisation_adulte'       => 'decimal:2',
        'cotisation_enfant'       => 'decimal:2',
        'cotisation_famille_base' => 'decimal:2',
        'cotisation_enfant_sup'   => 'decimal:2',
    ];

    public function cotisations(): HasMany
    {
        return $this->hasMany(Cotisation::class);
    }

    public function groupes(): HasMany
    {
        return $this->hasMany(Groupe::class);
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Categorie::class);
    }

    public function courriers(): HasMany
    {
        return $this->hasMany(Courrier::class);
    }

    /** Saison active courante */
    public static function active(): ?self
    {
        return static::where('active', true)->first();
    }

    public function getStatsCotisationsAttribute(): array
    {
        $total   = $this->cotisations()->count();
        $payees  = $this->cotisations()->where('statut', 'paye')->count();
        $envoyes = $this->cotisations()->where('statut', 'envoye')->count();
        $montant = $this->cotisations()->where('statut', 'paye')->sum('montant');

        return compact('total', 'payees', 'envoyes', 'montant');
    }
}
