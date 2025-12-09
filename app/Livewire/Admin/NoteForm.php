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
        $this->reset(['title', 'difficulties', 'recommendations', 'other', 'promotionId']);
    }

    public function save(): void
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'difficulties' => ['nullable', 'string'],
            'recommendations' => ['nullable', 'string'],
            'other' => ['nullable', 'string'],
            'promotionId' => ['nullable', 'uuid', 'exists:promotions,id'],
        ];

        $this->validate($rules, [
            'title.required' => 'Le titre de la note est obligatoire.',
        ]);

        // Au moins un des champs doit être rempli
        if (empty($this->difficulties) && empty($this->recommendations) && empty($this->other)) {
            $this->addError('difficulties', 'Au moins un des champs (Difficultés, Recommandations ou Autre) doit être rempli.');

            return;
        }

        PromotionNote::create([
            'admin_id' => $this->admin->id,
            'created_by' => Auth::id(),
            'title' => $this->title,
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
        $promotions = $this->admin->promotions()->orderBy('name')->get();

        return view('livewire.admin.note-form', [
            'promotions' => $promotions,
        ]);
    }
}
