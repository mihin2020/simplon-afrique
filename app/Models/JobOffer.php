<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobOffer extends Model
{
    use HasFactory, HasUuids;

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
        'title',
        'contract_type',
        'location',
        'remote_policy',
        'description',
        'experience_years',
        'minimum_education',
        'required_skills',
        'application_deadline',
        'additional_info',
        'attachment_path',
        'status',
        'published_at',
        'created_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'required_skills' => 'array',
            'application_deadline' => 'date',
            'published_at' => 'datetime',
        ];
    }

    /**
     * Get the user who created this job offer.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all applications for this job offer.
     */
    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    /**
     * Scope a query to only include published offers.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope a query to only include active offers (published and not expired).
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->published()
            ->where('application_deadline', '>=', now()->toDateString());
    }

    /**
     * Scope a query to only include draft offers.
     */
    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope a query to only include closed offers.
     */
    public function scopeClosed(Builder $query): Builder
    {
        return $query->where('status', 'closed');
    }

    /**
     * Check if the offer is published.
     */
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    /**
     * Check if the offer is still accepting applications.
     */
    public function isAcceptingApplications(): bool
    {
        return $this->isPublished() && $this->application_deadline->isFuture();
    }

    /**
     * Check if a user has already applied to this offer.
     */
    public function hasUserApplied(User $user): bool
    {
        return $this->applications()->where('user_id', $user->id)->exists();
    }

    /**
     * Get the contract type label in French.
     */
    public function getContractTypeLabelAttribute(): string
    {
        return match ($this->contract_type) {
            'cdi' => 'CDI',
            'cdd' => 'CDD',
            'stage' => 'Stage',
            'alternance' => 'Alternance',
            'freelance' => 'Freelance',
            default => $this->contract_type,
        };
    }

    /**
     * Get the remote policy label in French.
     */
    public function getRemotePolicyLabelAttribute(): string
    {
        return match ($this->remote_policy) {
            'sur_site' => 'Sur site',
            'hybride' => 'Hybride',
            'full_remote' => 'Full remote',
            default => $this->remote_policy,
        };
    }

    /**
     * Get the status label in French.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Brouillon',
            'published' => 'Publiée',
            'closed' => 'Clôturée',
            default => $this->status,
        };
    }
}
