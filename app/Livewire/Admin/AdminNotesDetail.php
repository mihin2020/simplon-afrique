<?php

namespace App\Livewire\Admin;

use App\Models\PromotionNote;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class AdminNotesDetail extends Component
{
    use WithPagination;

    public User $admin;

    public $showNoteForm = false;

    protected $paginationTheme = 'tailwind';

    public function mount(User $admin): void
    {
        $this->admin = $admin;
    }

    public function openNoteForm(): void
    {
        $this->showNoteForm = true;
        $this->dispatch('open-note-form');
    }

    public function closeNoteForm(): void
    {
        $this->showNoteForm = false;
        $this->dispatch('close-note-form');
    }

    protected $listeners = ['note-saved' => 'handleNoteSaved', 'note-form-closed' => 'handleNoteFormClosed'];

    public function handleNoteSaved(): void
    {
        $this->dispatch('close-note-form');
        $this->resetPage();
        session()->flash('message', 'Note ajoutée avec succès.');
    }

    public function handleNoteFormClosed(): void
    {
        $this->showNoteForm = false;
    }

    public function render()
    {
        $notes = PromotionNote::where('admin_id', $this->admin->id)
            ->with(['promotion', 'createdBy'])
            ->latest()
            ->paginate(10);

        return view('livewire.admin.admin-notes-detail', [
            'notes' => $notes,
        ]);
    }
}
