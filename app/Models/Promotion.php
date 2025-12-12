<?php

namespace App\Models;

use Database\Factories\PromotionFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Promotion extends Model
{
    use HasFactory, HasUuids;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): PromotionFactory
    {
        return PromotionFactory::new();
    }

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
        'start_date',
        'end_date',
        'country',
        'number_of_learners',
        'admin_id',
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
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    /**
     * Get all organizations associated with this promotion.
     */
    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class, 'promotion_organization')->withTimestamps();
    }

    /**
     * Get all formateurs associated with this promotion.
     */
    public function formateurs(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'promotion_formateur', 'promotion_id', 'user_id')->withTimestamps();
    }

    /**
     * Get the admin associated with this promotion.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Get the user who created this promotion.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all notes for this promotion.
     */
    public function notes(): HasMany
    {
        return $this->hasMany(PromotionNote::class);
    }
}
