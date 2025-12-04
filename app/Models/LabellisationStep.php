<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabellisationStep extends Model
{
    use HasUuids;

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
        'label',
        'display_order',
    ];

    /**
     * Get the candidatures currently at this step.
     */
    public function candidatures(): HasMany
    {
        return $this->hasMany(Candidature::class, 'current_step_id');
    }

    /**
     * Get all candidature step records for this labellisation step.
     */
    public function candidatureSteps(): HasMany
    {
        return $this->hasMany(CandidatureStep::class);
    }
}
