<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FormateurProfile extends Model
{
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'formateurs_profiles';

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
        'user_id',
        'photo_path',
        'phone_country_code',
        'phone_number',
        'country',
        'technical_profile',
        'years_of_experience',
        'portfolio_url',
    ];

    /**
     * Get the user associated with this formateur profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the certifications attached to this formateur profile.
     */
    public function certifications(): BelongsToMany
    {
        return $this->belongsToMany(
            CertificationTag::class,
            'certification_formateur',
            'formateur_profile_id',
            'certification_tag_id'
        )->withTimestamps();
    }
}
