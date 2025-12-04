<?php

namespace App\Livewire\Admin;

use App\Models\Candidature;
use Livewire\Component;

class SetGlobalScore extends Component
{
    public Candidature $candidature;

    public ?float $adminGlobalScore = null;

    public function mount(Candidature $candidature): void
    {
        $this->candidature = $candidature;
        $this->adminGlobalScore = $candidature->admin_global_score;
    }

    public function save(): void
    {
        $this->validate([
            'adminGlobalScore' => 'nullable|numeric|min:0|max:20',
        ], [
            'adminGlobalScore.numeric' => 'La note doit être un nombre.',
            'adminGlobalScore.min' => 'La note doit être supérieure ou égale à 0.',
            'adminGlobalScore.max' => 'La note doit être inférieure ou égale à 20.',
        ]);

        $this->candidature->update([
            'admin_global_score' => $this->adminGlobalScore,
        ]);

        session()->flash('success', 'La note globale de l\'administrateur a été enregistrée avec succès.');
    }

    public function render()
    {
        return view('livewire.admin.set-global-score');
    }
}
