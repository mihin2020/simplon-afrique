<?php

namespace App\Livewire\Admin;

use App\Mail\JobOfferPublishedMail;
use App\Models\JobApplication;
use App\Models\JobOffer;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class JobOfferDetail extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public JobOffer $jobOffer;

    public string $applicationStatusFilter = '';

    public string $applicantTypeFilter = '';

    public function mount(string $jobOfferId): void
    {
        $this->jobOffer = JobOffer::with('creator')->findOrFail($jobOfferId);
    }

    public function updatingApplicationStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingApplicantTypeFilter(): void
    {
        $this->resetPage();
    }

    public function publish(): void
    {
        if ($this->jobOffer->isPublished()) {
            session()->flash('error', 'Cette offre est déjà publiée.');

            return;
        }

        $this->jobOffer->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->sendPublicationEmails($this->jobOffer);

        session()->flash('success', 'L\'offre d\'emploi a été publiée avec succès.');
    }

    public function close(): void
    {
        $this->jobOffer->update(['status' => 'closed']);

        session()->flash('success', 'L\'offre d\'emploi a été clôturée.');
    }

    public function reopen(): void
    {
        if ($this->jobOffer->application_deadline->isPast()) {
            session()->flash('error', 'Impossible de réouvrir une offre dont la date limite est passée.');

            return;
        }

        $this->jobOffer->update(['status' => 'published']);

        session()->flash('success', 'L\'offre d\'emploi a été réouverte.');
    }

    public function updateApplicationStatus(string $applicationId, string $status): void
    {
        $application = JobApplication::findOrFail($applicationId);
        $application->update(['status' => $status]);

        session()->flash('success', 'Le statut de la candidature a été mis à jour.');
    }

    /**
     * Envoyer les emails de notification de publication (sans queue).
     * Optimisé pour envoyer à tous les destinataires en une seule requête SMTP.
     */
    protected function sendPublicationEmails(JobOffer $offer): void
    {
        $roleNames = ['admin', 'formateur'];
        $roleIds = Role::whereIn('name', $roleNames)->pluck('id');

        $recipients = User::whereHas('roles', function ($query) use ($roleIds) {
            $query->whereIn('roles.id', $roleIds);
        })->pluck('email')->toArray();

        if (empty($recipients)) {
            return;
        }

        $applyUrl = route('job-offers.detail', $offer);

        // Envoi optimisé : utiliser le premier destinataire en "to" et les autres en BCC
        $primaryRecipient = array_shift($recipients);

        try {
            $mail = new JobOfferPublishedMail($offer, $applyUrl);

            if (! empty($recipients)) {
                Mail::to($primaryRecipient)
                    ->bcc($recipients)
                    ->send($mail);
            } else {
                Mail::to($primaryRecipient)->send($mail);
            }
        } catch (\Exception $e) {
            report($e);
        }
    }

    public function render(): View
    {
        $applicationsQuery = $this->jobOffer->applications()
            ->with('user')
            ->latest();

        if ($this->applicationStatusFilter) {
            $applicationsQuery->where('status', $this->applicationStatusFilter);
        }

        if ($this->applicantTypeFilter) {
            $applicationsQuery->where('applicant_type', $this->applicantTypeFilter);
        }

        $applications = $applicationsQuery->paginate(10);

        $applicationStatusOptions = [
            'pending' => 'En attente',
            'reviewed' => 'Examinée',
            'accepted' => 'Acceptée',
            'rejected' => 'Refusée',
        ];

        $applicantTypeOptions = [
            'formateur' => 'Formateur',
            'admin' => 'Administrateur',
        ];

        return view('livewire.admin.job-offer-detail', [
            'applications' => $applications,
            'applicationStatusOptions' => $applicationStatusOptions,
            'applicantTypeOptions' => $applicantTypeOptions,
        ]);
    }
}
