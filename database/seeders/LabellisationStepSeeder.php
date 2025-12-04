<?php

namespace Database\Seeders;

use App\Models\LabellisationStep;
use Illuminate\Database\Seeder;

class LabellisationStepSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $steps = [
            ['name' => 'candidature', 'label' => 'Candidature', 'display_order' => 1],
            ['name' => 'technique', 'label' => 'Mise en situation technique', 'display_order' => 2],
            ['name' => 'pedagogique', 'label' => 'Mise en situation pédagogique', 'display_order' => 3],
            ['name' => 'entretien_evaluation', 'label' => 'Entretiens & Évaluation', 'display_order' => 4],
            ['name' => 'certification', 'label' => 'Décision & Certification', 'display_order' => 5],
        ];

        foreach ($steps as $step) {
            LabellisationStep::query()->firstOrCreate(
                ['name' => $step['name']],
                [
                    'label' => $step['label'],
                    'display_order' => $step['display_order'],
                ]
            );
        }
    }
}
