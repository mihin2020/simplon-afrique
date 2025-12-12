<?php

namespace App\Livewire\Admin;

use App\Models\PromotionNote;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NoteForm extends Component
{
    public User $admin;

    public $title = '';

    public $training_curriculum = '';

    public $difficulties = '';

    public $recommendations = '';

    public $other = '';

    public $promotionId = '';

    protected $listeners = ['open-note-form' => 'resetForm', 'close-note-form' => 'handleClose'];

    public function mount(User $admin): void
    {
        $this->admin = $admin;
    }

    public function resetForm(): void
    {
        $this->reset(['title', 'training_curriculum', 'difficulties', 'recommendations', 'other', 'promotionId']);
    }

    public function save(): void
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'training_curriculum' => ['nullable', 'string'],
            'difficulties' => ['nullable', 'string'],
            'recommendations' => ['nullable', 'string'],
            'other' => ['nullable', 'string'],
            'promotionId' => ['nullable', 'uuid', 'exists:promotions,id'],
        ];

        $this->validate($rules, [
            'title.required' => 'Le titre de la note est obligatoire.',
        ]);

        // Au moins un des champs doit être rempli
        if (empty($this->training_curriculum) && empty($this->difficulties) && empty($this->recommendations) && empty($this->other)) {
            $this->addError('training_curriculum', 'Au moins un des champs (Déroulé de la formation, Difficultés, Recommandations ou Autre) doit être rempli.');

            return;
        }

        PromotionNote::create([
            'admin_id' => $this->admin->id,
            'created_by' => Auth::id(),
            'title' => $this->title,
            'training_curriculum' => $this->training_curriculum ?: null,
            'difficulties' => $this->difficulties ?: null,
            'recommendations' => $this->recommendations ?: null,
            'other' => $this->other ?: null,
            'promotion_id' => $this->promotionId ?: null,
        ]);

        $this->dispatch('note-saved');
        $this->resetForm();
        $this->dispatch('close-note-form');
    }

    public function cancel(): void
    {
        $this->resetForm();
        $this->dispatch('close-note-form');
    }

    public function handleClose(): void
    {
        $this->resetForm();
    }

    public function render()
    {
        // Récupérer toutes les promotions (formateur + admin) avec leurs organisations
        $formateurPromotions = $this->admin->formateurPromotions()
            ->with('organizations')
            ->orderBy('name')
            ->get();
        $adminPromotions = $this->admin->promotions()
            ->with('organizations')
            ->orderBy('name')
            ->get();
        
        // Combiner et dédupliquer
        $allPromotions = $formateurPromotions->merge($adminPromotions)->unique('id')->sortBy('name');

        return view('livewire.admin.note-form', [
            'promotions' => $allPromotions,
        ]);
    }
}
