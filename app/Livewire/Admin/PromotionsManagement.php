<?php

namespace App\Livewire\Admin;

use App\Models\Organization;
use App\Models\Promotion;
use App\Models\Role;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class PromotionsManagement extends Component
{
    use WithPagination;

    public $showModal = false;

    public $editingPromotionId = null;

    public $search = '';

    protected $paginationTheme = 'tailwind';

    protected $listeners = ['promotion-saved' => 'handlePromotionSaved'];

    public function mount(): void
    {
        $this->resetPage();
    }

    public function handlePromotionSaved(): void
    {
        $this->closeModal();
        $this->resetPage();
    }

    public function updatedShowModal($value): void
    {
        if (! $value) {
            $this->editingPromotionId = null;
        }
    }

    public function isSuperAdmin(): bool
    {
        return auth()->user()->roles->contains('name', 'super_admin');
    }

    public function openModal(?string $promotionId = null): void
    {
        $this->editingPromotionId = $promotionId;
        $this->showModal = true;
        $this->dispatch('open-promotion-form', ['promotionId' => $promotionId]);
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->editingPromotionId = null;
        $this->dispatch('close-promotion-form');
    }

    public function delete(string $promotionId): void
    {
        $promotion = Promotion::findOrFail($promotionId);
        $promotion->delete();
        session()->flash('message', 'Promotion supprimée avec succès.');
    }

    public function render()
    {
        $query = Promotion::with(['organizations', 'admin', 'createdBy']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('country', 'like', '%'.$this->search.'%')
                    ->orWhereHas('organizations', function ($orgQ) {
                        $orgQ->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('admin', function ($adminQ) {
                        $adminQ->where('name', 'like', '%'.$this->search.'%')
                            ->orWhere('first_name', 'like', '%'.$this->search.'%')
                            ->orWhere('email', 'like', '%'.$this->search.'%');
                    });
            });
        }

        $promotions = $query->latest()->paginate(10);

        return view('livewire.admin.promotions-management', [
            'promotions' => $promotions,
        ]);
    }
}
