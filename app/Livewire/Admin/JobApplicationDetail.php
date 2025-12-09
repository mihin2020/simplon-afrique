<?php

namespace App\Livewire\Admin;

use App\Models\JobApplication;
use Illuminate\View\View;
use Livewire\Component;

class JobApplicationDetail extends Component
{
    public JobApplication $application;

    public string $notes = '';

    public string $status = '';

    public function mount(string $applicationId): void
    {
        $this->application = JobApplication::with(['jobOffer', 'user.formateurProfile'])
            ->findOrFail($applicationId);

        $this->notes = $this->application->notes ?? '';
        $this->status = $this->application->status;
    }

    public function updateStatus(string $newStatus): void
    {
        $this->application->update([
            'status' => $newStatus,
            'notes' => $this->notes,
        ]);

        $this->status = $newStatus;

        session()->flash('success', 'Le statut de la candidature a été mis à jour.');
    }

    public function saveNotes(): void
    {
        $this->application->update(['notes' => $this->notes]);

        session()->flash('success', 'Les notes ont été enregistrées.');
    }

    public function render(): View
    {
        $statusOptions = [
            'pending' => 'En attente',
            'reviewed' => 'Examinée',
            'accepted' => 'Acceptée',
            'rejected' => 'Refusée',
        ];

        return view('livewire.admin.job-application-detail', [
            'statusOptions' => $statusOptions,
        ]);
    }
}
