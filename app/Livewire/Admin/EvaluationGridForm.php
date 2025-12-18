<?php

namespace App\Livewire\Admin;

use App\Models\EvaluationGrid;
use Livewire\Component;

class EvaluationGridForm extends Component
{
    public ?string $gridId = null;

    public $name = '';

    public $description = '';

    public $isActive = true;

    protected $rules = [
        'name' => ['required', 'string', 'max:255'],
        'description' => ['nullable', 'string'],
        'isActive' => ['boolean'],
    ];

    protected $messages = [
        'name.required' => 'Le nom de la grille est obligatoire.',
        'name.max' => 'Le nom ne doit pas dépasser 255 caractères.',
    ];

    public function mount(?string $gridId = null): void
    {
        $this->gridId = $gridId;
        if ($gridId) {
            $grid = EvaluationGrid::findOrFail($gridId);
            $this->name = $grid->name;
            $this->description = $grid->description;
            $this->isActive = $grid->is_active;
        }
    }

    public function submit(): void
    {
        $this->validate();

        if ($this->gridId) {
            $grid = EvaluationGrid::findOrFail($this->gridId);
            $grid->update([
                'name' => $this->name,
                'description' => $this->description,
                'is_active' => $this->isActive,
            ]);
            session()->flash('success', 'La grille d\'évaluation a été modifiée avec succès.');
        } else {
            EvaluationGrid::create([
                'name' => $this->name,
                'description' => $this->description,
                'is_active' => $this->isActive,
            ]);
            session()->flash('success', 'La grille d\'évaluation a été créée avec succès.');
        }

        $this->redirect(route('admin.evaluation-grids'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.evaluation-grid-form');
    }
}









