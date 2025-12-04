<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $badges = [
            [
                'name' => 'junior',
                'label' => 'Label Formateur Junior',
                'min_score' => 10.0,
                'max_score' => 12.99,
            ],
            [
                'name' => 'intermediaire',
                'label' => 'Label Formateur Intermédiaire',
                'min_score' => 13.0,
                'max_score' => 15.99,
            ],
            [
                'name' => 'senior',
                'label' => 'Label Formateur Senior',
                'min_score' => 16.0,
                'max_score' => 20.0,
            ],
        ];

        foreach ($badges as $badge) {
            Badge::query()->firstOrCreate(
                ['name' => $badge['name']],
                [
                    'label' => $badge['label'],
                    'min_score' => $badge['min_score'],
                    'max_score' => $badge['max_score'],
                ]
            );
        }
    }
}
