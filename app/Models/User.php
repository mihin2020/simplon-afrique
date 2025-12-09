<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasUuids, Notifiable;

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
        'first_name',
        'email',
        'password',
        'is_referent_pedagogique',
        'country',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the roles associated with the user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    /**
     * Get the formateur profile linked to this user, if any.
     */
    public function formateurProfile(): HasOne
    {
        return $this->hasOne(FormateurProfile::class);
    }

    /**
     * Get all candidatures submitted by this user (as formateur).
     */
    public function candidatures(): HasMany
    {
        return $this->hasMany(Candidature::class);
    }

    /**
     * Get all jury memberships for this user.
     */
    public function juryMembers(): HasMany
    {
        return $this->hasMany(JuryMember::class);
    }

    /**
     * Get all job offers created by this user (super admin).
     */
    public function createdJobOffers(): HasMany
    {
        return $this->hasMany(JobOffer::class, 'created_by');
    }

    /**
     * Get all job applications submitted by this user.
     */
    public function jobApplications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    /**
     * Get all organizations associated with this user as a referent pédagogique.
     */
    public function referentOrganizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class, 'referent_organizations')->withTimestamps();
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    /**
     * Check if user is a super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if user is a formateur.
     */
    public function isFormateur(): bool
    {
        return $this->hasRole('formateur');
    }

    /**
     * Check if user is a referent pédagogique.
     */
    public function isReferentPedagogique(): bool
    {
        return (bool) $this->is_referent_pedagogique;
    }

    /**
     * Check if this referent can manage a specific formateur.
     */
    public function canManageFormateur(User $formateur): bool
    {
        // Si l'utilisateur n'est pas référent pédagogique, pas de restriction
        if (! $this->isReferentPedagogique()) {
            return true;
        }

        // Si le référent n'a pas de pays assigné, pas de restriction
        if (empty($this->country)) {
            return true;
        }

        $formateurProfile = $formateur->formateurProfile;

        // Si le formateur n'a pas de profil, on ne peut pas le gérer
        if (! $formateurProfile) {
            return false;
        }

        // Vérifier que le formateur est du même pays
        if ($formateurProfile->country !== $this->country) {
            return false;
        }

        // Si le référent a des organisations assignées, vérifier que le formateur en fait partie
        $referentOrganizations = $this->referentOrganizations()->pluck('organizations.id')->toArray();

        if (! empty($referentOrganizations)) {
            // Si le formateur n'a pas d'organisations, il n'est pas géré par ce référent
            $formateurOrganizations = $formateurProfile->organizations()->pluck('organizations.id')->toArray();
            if (empty($formateurOrganizations)) {
                return false;
            }

            // Vérifier qu'au moins une organisation du formateur est dans la liste du référent
            $commonOrganizations = array_intersect($formateurOrganizations, $referentOrganizations);
            if (empty($commonOrganizations)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Scope pour filtrer les formateurs selon le périmètre d'un référent pédagogique.
     * Si l'utilisateur n'est pas référent ou n'a pas de restrictions, aucun filtre n'est appliqué.
     */
    public function scopeForReferent($query, ?User $referent = null): void
    {
        // Si pas d'utilisateur, pas référent, ou pas de pays assigné → AUCUN FILTRE (admin classique)
        if (! $referent || ! $referent->isReferentPedagogique() || empty($referent->country)) {
            return;
        }

        // SEULEMENT pour les référents pédagogiques → appliquer le filtre
        $referentOrganizations = $referent->referentOrganizations()->pluck('organizations.id')->toArray();

        if (! empty($referentOrganizations)) {
            // Si le référent a des organisations assignées, afficher les formateurs qui :
            // - Sont du même pays
            // - ET (ont au moins une organisation dans la liste du référent OU n'ont aucune organisation)
            $query->whereHas('formateurProfile', function ($q) use ($referent, $referentOrganizations) {
                $q->where('country', $referent->country)
                    ->where(function ($subQ) use ($referentOrganizations) {
                        // Soit le formateur a au moins une organisation dans la liste du référent
                        $subQ->whereHas('organizations', function ($orgQ) use ($referentOrganizations) {
                            $orgQ->whereIn('organizations.id', $referentOrganizations);
                        })
                        // Soit le formateur n'a aucune organisation
                            ->orDoesntHave('organizations');
                    });
            });
        } else {
            // Si le référent n'a pas d'organisations assignées, afficher tous les formateurs du même pays
            $query->whereHas('formateurProfile', function ($q) use ($referent) {
                $q->where('country', $referent->country);
            });
        }
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
