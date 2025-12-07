<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AttestationSetting extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'director_name',
        'director_title',
        'signature_path',
        'logo_path',
        'organization_name',
        'attestation_text',
    ];

    /**
     * Récupère ou crée les paramètres d'attestation (singleton).
     */
    public static function getSettings(): self
    {
        $settings = self::first();

        if (! $settings) {
            $settings = self::create([
                'organization_name' => 'Simplon Africa',
                'director_title' => 'Directeur Général',
                'attestation_text' => 'Nous certifions que le/la formateur(trice) mentionné(e) ci-dessus a satisfait aux exigences du processus de labellisation et s\'est vu attribuer le badge correspondant à son niveau de compétences.',
            ]);
        }

        return $settings;
    }
}
