<?php

namespace App\Livewire\Admin;

use App\Models\JobOffer;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class JobOffersManagement extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public string $search = '';

    public string $statusFilter = '';

    public string $contractTypeFilter = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingContractTypeFilter(): void
    {
        $this->resetPage();
    }

    public function deleteOffer(string $offerId): void
    {
        $offer = JobOffer::findOrFail($offerId);

        $applicationsCount = $offer->applications()->count();

        // Supprimer l'offre (les candidatures seront supprimées en cascade grâce à la contrainte foreign key)
        $offer->delete();

        if ($applicationsCount > 0) {
            session()->flash('success', "L'offre d'emploi et ses {$applicationsCount} candidature(s) ont été supprimées avec succès.");
        } else {
            session()->flash('success', 'L\'offre d\'emploi a été supprimée avec succès.');
        }
    }

    public function closeOffer(string $offerId): void
    {
        $offer = JobOffer::findOrFail($offerId);
        $offer->update(['status' => 'closed']);

        session()->flash('success', 'L\'offre d\'emploi a été clôturée avec succès.');
    }

    public function render(): View
    {
        $query = JobOffer::with(['creator', 'applications'])
            ->latest();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%'.$this->search.'%')
                    ->orWhere('location', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->contractTypeFilter) {
            $query->where('contract_type', $this->contractTypeFilter);
        }

        $jobOffers = $query->paginate(15);

        $statusOptions = [
            'draft' => 'Brouillon',
            'published' => 'Publiée',
            'closed' => 'Clôturée',
        ];

        $contractTypeOptions = [
            'cdi' => 'CDI',
            'cdd' => 'CDD',
            'stage' => 'Stage',
            'alternance' => 'Alternance',
            'freelance' => 'Freelance',
        ];

        return view('livewire.admin.job-offers-management', [
            'jobOffers' => $jobOffers,
            'statusOptions' => $statusOptions,
            'contractTypeOptions' => $contractTypeOptions,
        ]);
    }
}
