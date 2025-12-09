<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CertificationTag extends Model
{
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'certifications_tags';

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
    ];

    /**
     * Get all formateur profiles that have this certification.
     */
    public function formateurProfiles(): BelongsToMany
    {
        return $this->belongsToMany(
            FormateurProfile::class,
            'certification_formateur',
            'certification_tag_id',
            'formateur_profile_id'
        )->withTimestamps();
    }

    /**
     * Scope pour filtrer les certifications selon le périmètre d'un référent pédagogique.
     * Si l'utilisateur n'est pas référent ou n'a pas de restrictions, aucun filtre n'est appliqué.
     */
    public function scopeForReferent($query, ?User $referent = null): void
    {
        // Si pas d'utilisateur, pas référent, ou pas de pays assigné → AUCUN FILTRE (admin classique)
        if (! $referent || ! $referent->isReferentPedagogique() || empty($referent->country)) {
            return;
        }

        // SEULEMENT pour les référents pédagogiques → appliquer le filtre
        $query->whereHas('formateurProfiles', function ($q) use ($referent) {
            $q->where('country', $referent->country);

            $referentOrganizations = $referent->referentOrganizations()->pluck('organizations.id')->toArray();
            if (! empty($referentOrganizations)) {
                $q->whereIn('organization_id', $referentOrganizations);
            }
        });
    }
}

     * Scope pour filtrer les certifications selon le périmètre d'un référent pédagogique.
     * Si l'utilisateur n'est pas référent ou n'a pas de restrictions, aucun filtre n'est appliqué.
     */
    public function scopeForReferent($query, ?User $referent = null): void
    {
        // Si pas d'utilisateur, pas référent, ou pas de pays assigné → AUCUN FILTRE (admin classique)
        if (! $referent || ! $referent->isReferentPedagogique() || empty($referent->country)) {
            return;
        }

        // SEULEMENT pour les référents pédagogiques → appliquer le filtre
        $query->whereHas('formateurProfiles', function ($q) use ($referent) {
            $q->where('country', $referent->country);

            $referentOrganizations = $referent->referentOrganizations()->pluck('organizations.id')->toArray();
            if (! empty($referentOrganizations)) {
                $q->whereIn('organization_id', $referentOrganizations);
            }
        });
    }
}

     * Scope pour filtrer les certifications selon le périmètre d'un référent pédagogique.
     * Si l'utilisateur n'est pas référent ou n'a pas de restrictions, aucun filtre n'est appliqué.
     */
    public function scopeForReferent($query, ?User $referent = null): void
    {
        // Si pas d'utilisateur, pas référent, ou pas de pays assigné → AUCUN FILTRE (admin classique)
        if (! $referent || ! $referent->isReferentPedagogique() || empty($referent->country)) {
            return;
        }

        // SEULEMENT pour les référents pédagogiques → appliquer le filtre
        $query->whereHas('formateurProfiles', function ($q) use ($referent) {
            $q->where('country', $referent->country);

            $referentOrganizations = $referent->referentOrganizations()->pluck('organizations.id')->toArray();
            if (! empty($referentOrganizations)) {
                $q->whereIn('organization_id', $referentOrganizations);
            }
        });
    }
}

     * Scope pour filtrer les certifications selon le périmètre d'un référent pédagogique.
     * Si l'utilisateur n'est pas référent ou n'a pas de restrictions, aucun filtre n'est appliqué.
     */
    public function scopeForReferent($query, ?User $referent = null): void
    {
        // Si pas d'utilisateur, pas référent, ou pas de pays assigné → AUCUN FILTRE (admin classique)
        if (! $referent || ! $referent->isReferentPedagogique() || empty($referent->country)) {
            return;
        }

        // SEULEMENT pour les référents pédagogiques → appliquer le filtre
        $query->whereHas('formateurProfiles', function ($q) use ($referent) {
            $q->where('country', $referent->country);

            $referentOrganizations = $referent->referentOrganizations()->pluck('organizations.id')->toArray();
            if (! empty($referentOrganizations)) {
                $q->whereIn('organization_id', $referentOrganizations);
            }
        });
    }
}

     * Scope pour filtrer les certifications selon le périmètre d'un référent pédagogique.
     * Si l'utilisateur n'est pas référent ou n'a pas de restrictions, aucun filtre n'est appliqué.
     */
    public function scopeForReferent($query, ?User $referent = null): void
    {
        // Si pas d'utilisateur, pas référent, ou pas de pays assigné → AUCUN FILTRE (admin classique)
        if (! $referent || ! $referent->isReferentPedagogique() || empty($referent->country)) {
            return;
        }

        // SEULEMENT pour les référents pédagogiques → appliquer le filtre
        $query->whereHas('formateurProfiles', function ($q) use ($referent) {
            $q->where('country', $referent->country);

            $referentOrganizations = $referent->referentOrganizations()->pluck('organizations.id')->toArray();
            if (! empty($referentOrganizations)) {
                $q->whereIn('organization_id', $referentOrganizations);
            }
        });
    }
}
