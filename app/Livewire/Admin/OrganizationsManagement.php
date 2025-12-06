<?php

namespace App\Livewire\Admin;

use App\Models\Organization;
use Livewire\Component;
use Livewire\WithPagination;

class OrganizationsManagement extends Component
{
    use WithPagination;

    public $showModal = false;

    public $editingOrganizationId = null;

    public $name = '';

    public $search = '';

    protected $paginationTheme = 'tailwind';

    public function mount(): void
    {
        $this->resetPage();
    }

    public function isSuperAdmin(): bool
    {
        return auth()->user()->roles->contains('name', 'super_admin');
    }

    public function openModal(?string $organizationId = null): void
    {
        $this->editingOrganizationId = $organizationId;
        $this->showModal = true;

        if ($organizationId) {
            $organization = Organization::findOrFail($organizationId);
            $this->name = $organization->name;
        } else {
            $this->reset(['name']);
        }
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset(['editingOrganizationId', 'name']);
    }

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255', 'unique:organizations,name,'.$this->editingOrganizationId],
        ]);

        if ($this->editingOrganizationId) {
            $organization = Organization::findOrFail($this->editingOrganizationId);
            $organization->update([
                'name' => trim($this->name),
            ]);
            session()->flash('message', 'Organisation modifiée avec succès.');
        } else {
            Organization::create([
                'name' => trim($this->name),
            ]);
            session()->flash('message', 'Organisation créée avec succès.');
        }

        $this->closeModal();
    }

    public function delete(string $organizationId): void
    {
        $organization = Organization::findOrFail($organizationId);

        // Vérifier si l'organisation est utilisée
        if ($organization->formateurProfiles()->count() > 0) {
            session()->flash('error', 'Cette organisation ne peut pas être supprimée car elle est utilisée par des formateurs.');

            return;
        }

        $organization->delete();
        session()->flash('message', 'Organisation supprimée avec succès.');
    }

    public function render()
    {
        $query = Organization::query();

        if ($this->search) {
            $query->where('name', 'like', '%'.$this->search.'%');
        }

        $organizations = $query->orderBy('name')->paginate(10);

        return view('livewire.admin.organizations-management', [
            'organizations' => $organizations,
        ]);
    }
}
