<?php

namespace App\Livewire\Admin;

use App\Models\Organization;
use Livewire\Component;

class OrganizationsManagement extends Component
{

    public $search = '';
    public $showBulkModal = false;
    public $organizationsToAdd = [];
    public $newOrganizationName = '';
    public $bulkCountry = '';
    public $showEditModal = false;
    public $editingOrganizationId = null;
    public $editName = '';
    public $editCountry = '';

    public function openBulkModal(): void
    {
        $this->showBulkModal = true;
        $this->reset(['organizationsToAdd', 'newOrganizationName', 'bulkCountry']);
    }

    public function closeBulkModal(): void
    {
        $this->showBulkModal = false;
        $this->reset(['organizationsToAdd', 'newOrganizationName', 'bulkCountry']);
    }

    public function addOrganizationToBulk(): void
    {
        $this->validate([
            'newOrganizationName' => ['required', 'string', 'max:255'],
        ], [
            'newOrganizationName.required' => 'Le nom de l\'organisation est requis.',
        ]);

        $name = trim($this->newOrganizationName);

        $exists = collect($this->organizationsToAdd)->contains('name', $name);
        if ($exists) {
            session()->flash('error', 'Cette organisation est déjà dans la liste.');
            return;
        }

        $existsInDb = Organization::where('name', $name)->exists();
        if ($existsInDb) {
            session()->flash('error', 'Cette organisation existe déjà.');
            return;
        }

        $this->organizationsToAdd[] = [
            'name' => $name,
            'country' => $this->bulkCountry ?: null,
        ];

        $this->reset(['newOrganizationName']);
    }

    public function removeOrganizationFromBulk(int $index): void
    {
        unset($this->organizationsToAdd[$index]);
        $this->organizationsToAdd = array_values($this->organizationsToAdd);
    }

    public function saveBulk(): void
    {
        if (empty($this->organizationsToAdd)) {
            session()->flash('error', 'Veuillez ajouter au moins une organisation.');
            return;
        }

        $created = 0;
        $errors = [];

        foreach ($this->organizationsToAdd as $org) {
            if (Organization::where('name', $org['name'])->exists()) {
                $errors[] = $org['name'];
                continue;
            }

            Organization::create([
                'name' => $org['name'],
                'country' => $org['country'] ?: null,
            ]);
            $created++;
        }

        if ($created > 0) {
            session()->flash('message', $created.' organisation(s) créée(s) avec succès.');
        }

        if (!empty($errors)) {
            session()->flash('error', 'Les organisations suivantes existent déjà : '.implode(', ', $errors));
        }

        $this->closeBulkModal();
    }

    public function openEditModal(string $organizationId): void
    {
        $this->editingOrganizationId = $organizationId;
        $organization = Organization::findOrFail($organizationId);
        $this->editName = $organization->name;
        $this->editCountry = $organization->country ?? '';
        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->reset(['editingOrganizationId', 'editName', 'editCountry']);
    }

    public function updateOrganization(): void
    {
        $this->validate([
            'editName' => ['required', 'string', 'max:255', 'unique:organizations,name,'.$this->editingOrganizationId],
            'editCountry' => ['nullable', 'string', 'max:255'],
        ], [
            'editName.required' => 'Le nom de l\'organisation est requis.',
            'editName.unique' => 'Ce nom d\'organisation existe déjà.',
        ]);

        $organization = Organization::findOrFail($this->editingOrganizationId);
        $organization->update([
            'name' => trim($this->editName),
            'country' => $this->editCountry ?: null,
        ]);

        session()->flash('message', 'Organisation modifiée avec succès.');
        $this->closeEditModal();
    }

    public function delete(string $organizationId): void
    {
        $organization = Organization::findOrFail($organizationId);

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
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                  ->orWhere('country', 'like', '%'.$this->search.'%');
            });
        }

        $organizations = $query->with('formateurProfiles')->orderBy('country')->orderBy('name')->get();
        $groupedOrganizations = $organizations->groupBy('country');

        return view('livewire.admin.organizations-management', [
            'groupedOrganizations' => $groupedOrganizations,
            'countries' => \App\Data\CountriesData::getCountries(),
        ]);
    }
}
