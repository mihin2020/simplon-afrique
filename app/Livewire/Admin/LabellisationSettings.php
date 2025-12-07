<?php

namespace App\Livewire\Admin;

use App\Models\Badge;
use App\Models\LabellisationSetting;
use Livewire\Component;

class LabellisationSettings extends Component
{
    public $noteScale = 20;

    public $badges = [];

    protected function rules(): array
    {
        return [
            'noteScale' => ['required', 'integer', 'min:5', 'max:100'],
            'badges.*.min_score' => ['required', 'numeric', 'min:0'],
            'badges.*.max_score' => ['required', 'numeric', 'min:0'],
        ];
    }

    protected $messages = [
        'noteScale.required' => 'L\'échelle de notation est obligatoire.',
        'noteScale.integer' => 'L\'échelle doit être un nombre entier.',
        'noteScale.min' => 'L\'échelle doit être au minimum de 5.',
        'badges.*.min_score.required' => 'Le score minimum est obligatoire.',
        'badges.*.max_score.required' => 'Le score maximum est obligatoire.',
    ];

    public function mount(): void
    {
        // Charger l'échelle de notation
        $this->noteScale = (int) LabellisationSetting::getValue('note_scale', 20);

        // Charger les badges avec leurs seuils
        $this->loadBadges();
    }

    public function loadBadges(): void
    {
        $badges = Badge::orderBy('min_score')->get();

        $this->badges = $badges->map(function ($badge) {
            return [
                'id' => $badge->id,
                'name' => $badge->name,
                'label' => $badge->label,
                'min_score' => $badge->min_score ?? 0,
                'max_score' => $badge->max_score ?? 20,
            ];
        })->toArray();

        // Si aucun badge n'existe, créer les badges par défaut
        if (empty($this->badges)) {
            $this->createDefaultBadges();
        }
    }

    public function createDefaultBadges(): void
    {
        $defaultBadges = [
            ['name' => 'junior', 'label' => 'Label Formateur Junior', 'min_score' => 10, 'max_score' => 12.99],
            ['name' => 'intermediaire', 'label' => 'Label Formateur Intermédiaire', 'min_score' => 13, 'max_score' => 15.99],
            ['name' => 'senior', 'label' => 'Label Formateur Senior', 'min_score' => 16, 'max_score' => 20],
        ];

        foreach ($defaultBadges as $badgeData) {
            Badge::create($badgeData);
        }

        $this->loadBadges();
    }

    public function saveNoteScale(): void
    {
        $this->validate([
            'noteScale' => ['required', 'integer', 'min:5', 'max:100'],
        ]);

        LabellisationSetting::setValue('note_scale', (string) $this->noteScale);

        session()->flash('success', 'Échelle de notation mise à jour avec succès.');
    }

    public function saveBadgeThresholds(): void
    {
        $this->validate([
            'badges.*.min_score' => ['required', 'numeric', 'min:0'],
            'badges.*.max_score' => ['required', 'numeric', 'min:0'],
        ]);

        // Vérifier la cohérence des seuils
        foreach ($this->badges as $index => $badge) {
            if ($badge['min_score'] >= $badge['max_score']) {
                $this->addError("badges.{$index}.min_score", 'Le score minimum doit être inférieur au score maximum.');

                return;
            }
        }

        // Mettre à jour les badges
        foreach ($this->badges as $badgeData) {
            Badge::where('id', $badgeData['id'])->update([
                'min_score' => $badgeData['min_score'],
                'max_score' => $badgeData['max_score'],
            ]);
        }

        session()->flash('success', 'Seuils des badges mis à jour avec succès.');
    }

    public function render()
    {
        return view('livewire.admin.labellisation-settings');
    }
}
