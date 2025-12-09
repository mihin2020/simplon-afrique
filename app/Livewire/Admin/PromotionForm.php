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

    public $numberOfLearners = '';

    public $adminId = '';

    protected $listeners = [
        'open-promotion-form' => 'openForm',
        'close-promotion-form' => 'resetForm',
        'promotion-saved' => 'handlePromotionSaved',
    ];

    public function openForm(array $data = []): void
    {
        $promotionId = $data['promotionId'] ?? null;

        if ($promotionId) {
            $promotion = Promotion::with('organizations')->findOrFail($promotionId);
            $this->promotionId = $promotion->id;
            $this->name = $promotion->name;
            $this->startDate = $promotion->start_date->format('Y-m-d');
            $this->endDate = $promotion->end_date->format('Y-m-d');
            $this->country = $promotion->country;
            $this->selectedOrganizations = $promotion->organizations->pluck('id')->toArray();
            $this->numberOfLearners = $promotion->number_of_learners;
            $this->adminId = $promotion->admin_id;
        } else {
            $this->resetForm();
        }
    }

    public function resetForm(): void
    {
        $this->reset(['promotionId', 'name', 'startDate', 'endDate', 'country', 'selectedOrganizations', 'numberOfLearners', 'adminId']);
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
            'numberOfLearners' => ['required', 'integer', 'min:1'],
            'adminId' => ['required', 'uuid', 'exists:users,id'],
        ];

        $this->validate($rules, [
            'name.required' => 'Le nom de la promotion est obligatoire.',
            'startDate.required' => 'La date de début est obligatoire.',
            'endDate.required' => 'La date de fin est obligatoire.',
            'endDate.after' => 'La date de fin doit être postérieure à la date de début.',
            'country.required' => 'Le pays est obligatoire.',
            'selectedOrganizations.array' => 'Les organisations doivent être un tableau.',
            'selectedOrganizations.*.exists' => 'Une ou plusieurs organisations sélectionnées n\'existent pas.',
            'numberOfLearners.required' => 'Le nombre d\'apprenants est obligatoire.',
            'numberOfLearners.min' => 'Le nombre d\'apprenants doit être au moins 1.',
            'adminId.required' => 'L\'administrateur associé est obligatoire.',
        ]);

        $data = [
            'name' => $this->name,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'country' => $this->country,
            'number_of_learners' => $this->numberOfLearners,
            'admin_id' => $this->adminId,
            'created_by' => Auth::id(),
        ];

        if ($this->promotionId) {
            $promotion = Promotion::findOrFail($this->promotionId);
            $promotion->update($data);
            $promotion->organizations()->sync($this->selectedOrganizations ?? []);
            session()->flash('message', 'Promotion mise à jour avec succès.');
        } else {
            $promotion = Promotion::create($data);
            $promotion->organizations()->sync($this->selectedOrganizations ?? []);
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

    public function render()
    {
        $adminRole = Role::where('name', 'admin')->first();
        $admins = $adminRole ? User::whereHas('roles', function ($query) use ($adminRole) {
            $query->where('roles.id', $adminRole->id);
        })->orderBy('name')->get() : collect();

        return view('livewire.admin.promotion-form', [
            'organizations' => Organization::orderBy('name')->get(),
            'countries' => CountriesData::getCountries(),
            'admins' => $admins,
        ]);
    }
}
