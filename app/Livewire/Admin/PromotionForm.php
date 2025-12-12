<?php

namespace App\Livewire\Admin;

use App\Data\CountriesData;
use App\Models\Organization;
use App\Models\Promotion;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PromotionForm extends Component
{
    public $promotionId = null;

    public $name = '';

    public $startDate = '';

    public $endDate = '';

    public $country = '';

    public $selectedOrganizations = [];

    public $selectedFormateurs = [];

    public $numberOfLearners = '';

    protected $listeners = [
        'open-promotion-form' => 'openForm',
        'close-promotion-form' => 'resetForm',
        'promotion-saved' => 'handlePromotionSaved',
    ];

    public function mount(?string $promotionId = null): void
    {
        $this->promotionId = $promotionId;
        if ($promotionId) {
            $this->loadPromotion($promotionId);
        } else {
            $this->resetForm();
        }
    }

    public function openForm(array $data = []): void
    {
        $promotionId = $data['promotionId'] ?? null;

        if ($promotionId) {
            $this->loadPromotion($promotionId);
        } else {
            $this->resetForm();
        }
    }

    protected function loadPromotion(string $promotionId): void
    {
        $promotion = Promotion::with(['organizations', 'formateurs'])->findOrFail($promotionId);
        $this->promotionId = $promotion->id;
        $this->name = $promotion->name;
        $this->startDate = $promotion->start_date->format('Y-m-d');
        $this->endDate = $promotion->end_date->format('Y-m-d');
        $this->country = $promotion->country;
        $this->selectedOrganizations = $promotion->organizations->pluck('id')->toArray();
        $this->selectedFormateurs = $promotion->formateurs->pluck('id')->toArray();
        $this->numberOfLearners = $promotion->number_of_learners;
    }

    public function resetForm(): void
    {
        $this->reset(['promotionId', 'name', 'startDate', 'endDate', 'country', 'selectedOrganizations', 'selectedFormateurs', 'numberOfLearners']);
    }

    public function save(): void
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'startDate' => ['required', 'date'],
            'endDate' => ['required', 'date', 'after:startDate'],
            'country' => ['required', 'string', 'max:255'],
            'selectedOrganizations' => ['nullable', 'array'],
            'selectedOrganizations.*' => ['uuid', 'exists:organizations,id'],
            'selectedFormateurs' => ['required', 'array', 'min:1'],
            'selectedFormateurs.*' => ['uuid', 'exists:users,id'],
            'numberOfLearners' => ['required', 'integer', 'min:1'],
        ];

        $this->validate($rules, [
            'name.required' => 'Le nom de la promotion est obligatoire.',
            'startDate.required' => 'La date de début est obligatoire.',
            'endDate.required' => 'La date de fin est obligatoire.',
            'endDate.after' => 'La date de fin doit être postérieure à la date de début.',
            'country.required' => 'Le pays est obligatoire.',
            'selectedFormateurs.required' => 'Au moins un formateur doit être sélectionné.',
            'selectedFormateurs.min' => 'Au moins un formateur doit être sélectionné.',
            'numberOfLearners.required' => 'Le nombre d\'apprenants est obligatoire.',
            'numberOfLearners.min' => 'Le nombre d\'apprenants doit être au moins 1.',
        ]);

        $data = [
            'name' => $this->name,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'country' => $this->country,
            'number_of_learners' => $this->numberOfLearners,
            'created_by' => Auth::id(),
        ];

        if ($this->promotionId) {
            $promotion = Promotion::findOrFail($this->promotionId);
            $promotion->update($data);
            $promotion->organizations()->sync($this->selectedOrganizations ?? []);
            $promotion->formateurs()->sync($this->selectedFormateurs ?? []);
            session()->flash('message', 'Promotion mise à jour avec succès.');
        } else {
            $promotion = Promotion::create($data);
            $promotion->organizations()->sync($this->selectedOrganizations ?? []);
            $promotion->formateurs()->sync($this->selectedFormateurs ?? []);
            session()->flash('message', 'Promotion créée avec succès.');
        }

        $this->dispatch('promotion-saved');
        $this->dispatch('close-promotion-form');
        $this->resetForm();
    }

    public function handlePromotionSaved(): void
    {
        $this->resetForm();
    }

    public function cancel(): void
    {
        $this->resetForm();
        $this->dispatch('close-promotion-form');
    }

    public function removeOrganization(string $organizationId): void
    {
        $this->selectedOrganizations = array_values(array_diff($this->selectedOrganizations, [$organizationId]));
    }

    public function removeFormateur(string $formateurId): void
    {
        $this->selectedFormateurs = array_values(array_diff($this->selectedFormateurs, [$formateurId]));
    }

    public function updatedCountry($value): void
    {
        $this->selectedOrganizations = [];
    }

    public function render()
    {
        $superAdminRole = Role::where('name', 'super_admin')->first();
        $formateursQuery = User::query()->with('roles');

        if ($superAdminRole) {
            $formateursQuery->whereDoesntHave('roles', function ($query) use ($superAdminRole) {
                $query->where('roles.id', $superAdminRole->id);
            });
        }

        $formateurs = $formateursQuery->orderBy('name')->get();

        // Charger les formateurs sélectionnés même s'ils ne sont pas dans la liste filtrée (pour l'édition)
        $selectedFormateursData = collect();
        if (!empty($this->selectedFormateurs)) {
            $selectedFormateursData = User::whereIn('id', $this->selectedFormateurs)
                ->with('roles')
                ->get()
                ->filter(function ($user) use ($superAdminRole) {
                    if (!$superAdminRole) {
                        return true;
                    }
                    return !$user->roles->contains('id', $superAdminRole->id);
                });
        }

        $organizationsQuery = Organization::query();
        if ($this->country) {
            $organizationsQuery->where('country', $this->country);
        }
        $organizations = $organizationsQuery->orderBy('name')->get();

        // Charger les organisations sélectionnées même si elles ne sont pas dans la liste filtrée (pour l'édition)
        $selectedOrganizationsData = collect();
        if (!empty($this->selectedOrganizations)) {
            $selectedOrganizationsData = Organization::whereIn('id', $this->selectedOrganizations)
                ->orderBy('name')
                ->get();
        }

        return view('livewire.admin.promotion-form', [
            'organizations' => $organizations,
            'countries' => CountriesData::getCountries(),
            'formateurs' => $formateurs,
            'selectedFormateursData' => $selectedFormateursData,
            'selectedOrganizationsData' => $selectedOrganizationsData,
        ]);
    }
}
