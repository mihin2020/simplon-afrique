<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobApplication extends Model
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
        'job_offer_id',
        'user_id',
        'applicant_type',
        'cv_path',
        'profile_snapshot',
        'status',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'profile_snapshot' => 'array',
        ];
    }

    /**
     * Get the job offer for this application.
     */
    public function jobOffer(): BelongsTo
    {
        return $this->belongsTo(JobOffer::class);
    }

    /**
     * Get the user who submitted this application.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include pending applications.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include reviewed applications.
     */
    public function scopeReviewed(Builder $query): Builder
    {
        return $query->where('status', 'reviewed');
    }

    /**
     * Scope a query to filter by applicant type.
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('applicant_type', $type);
    }

    /**
     * Check if the applicant is a formateur.
     */
    public function isFormateur(): bool
    {
        return $this->applicant_type === 'formateur';
    }

    /**
     * Check if the applicant is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->applicant_type === 'admin';
    }

    /**
     * Get the applicant type label in French.
     */
    public function getApplicantTypeLabelAttribute(): string
    {
        return match ($this->applicant_type) {
            'formateur' => 'Formateur',
            'admin' => 'Administrateur',
            default => $this->applicant_type,
        };
    }

    /**
     * Get the status label in French.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'En attente',
            'reviewed' => 'Examinée',
            'accepted' => 'Acceptée',
            'rejected' => 'Refusée',
            default => $this->status,
        };
    }

    /**
     * Get the status color for UI display.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'yellow',
            'reviewed' => 'blue',
            'accepted' => 'green',
            'rejected' => 'red',
            default => 'gray',
        };
    }
}
