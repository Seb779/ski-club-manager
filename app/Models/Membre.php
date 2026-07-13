<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Membre extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'parent_id', 'prenom', 'nom', 'date_naissance', 'email',
        'telephone', 'adresse', 'npa', 'localite',
        'type', 'preference_envoi', 'actif', 'moniteur', 'notes',
    ];

    protected $casts = [
        'date_naissance'  => 'date',
        'actif'           => 'boolean',
        'moniteur'        => 'boolean',
    ];

    // ── Relations ─────────────────────────────────────────────────────────────

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Membre::class, 'parent_id');
    }

    public function enfants(): HasMany
    {
        return $this->hasMany(Membre::class, 'parent_id');
    }

    public function cotisations(): HasMany
    {
        return $this->hasMany(Cotisation::class);
    }

    public function groupes(): BelongsToMany
    {
        return $this->belongsToMany(Groupe::class, 'groupe_membre');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class);
    }

    public function groupesMoniteur(): HasMany
    {
        return $this->hasMany(Groupe::class, 'moniteur_id');
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getNomCompletAttribute(): string
    {
        return "{$this->prenom} {$this->nom}";
    }

    public function getInitialesAttribute(): string
    {
        return strtoupper(substr($this->prenom, 0, 1) . substr($this->nom, 0, 1));
    }

    public function getAgeAttribute(): ?int
    {
        return $this->date_naissance?->age;
    }

    public function getAnneeNaissanceAttribute(): ?int
    {
        return $this->date_naissance?->year;
    }

    /** Détermine la catégorie de course selon l'année de naissance et la saison */
    public function getCategorieForSaison(Saison $saison): ?Categorie
    {
        if (! $this->annee_naissance) {
            return null;
        }

        return $saison->categories()
            ->where('annee_naissance_min', '<=', $this->annee_naissance)
            ->where('annee_naissance_max', '>=', $this->annee_naissance)
            ->first();
    }

    /** Cotisation pour la saison donnée */
    public function cotisationPourSaison(int $saisonId): ?Cotisation
    {
        return $this->cotisations()->where('saison_id', $saisonId)->first();
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActifs($query)
    {
        return $query->where('actif', true);
    }

    public function scopeEnfants($query)
    {
        return $query->whereNotNull('parent_id')->orWhere('type', 'enfant');
    }

    public function scopeChefsFamille($query)
    {
        return $query->where('type', 'chef_famille');
    }

    public function scopeIndividuels($query)
    {
        return $query->where('type', 'individuel');
    }
}
