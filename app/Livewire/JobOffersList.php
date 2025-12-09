<?php

namespace App\Livewire;

use App\Mail\NewJobApplicationMail;
use App\Models\JobApplication;
use App\Models\JobOffer;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class JobOffersList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public string $search = '';

    public string $contractTypeFilter = '';

    public string $remotePolicyFilter = '';

    public ?string $selectedOfferId = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingContractTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatingRemotePolicyFilter(): void
    {
        $this->resetPage();
    }

    public function showOfferDetail(string $offerId): void
    {
        $this->selectedOfferId = $offerId;
    }

    public function closeOfferDetail(): void
    {
        $this->selectedOfferId = null;
    }

    public function apply(string $offerId): void
    {
        /** @var User $user */
        $user = Auth::user();
        $offer = JobOffer::findOrFail($offerId);

        // Vérifier si l'offre accepte encore les candidatures
        if (! $offer->isAcceptingApplications()) {
            session()->flash('error', 'Cette offre n\'accepte plus de candidatures.');

            return;
        }

        // Vérifier si l'utilisateur a déjà postulé
        if ($offer->hasUserApplied($user)) {
            session()->flash('error', 'Vous avez déjà postulé à cette offre.');

            return;
        }

        // Déterminer le type de candidat
        $isFormateur = $user->hasRole('formateur');
        $applicantType = $isFormateur ? 'formateur' : 'admin';

        // Construire le snapshot du profil
        $profileSnapshot = $this->buildProfileSnapshot($user, $isFormateur);

        // Gérer le CV pour les formateurs
        $cvPath = null;
        if ($isFormateur && $user->formateurProfile?->cv_path) {
            // Copier le CV pour le conserver même si le formateur le modifie
            $originalCvPath = $user->formateurProfile->cv_path;
            if (Storage::disk('public')->exists($originalCvPath)) {
                $newCvPath = 'job-applications/cv/'.uniqid().'_'.basename($originalCvPath);
                Storage::disk('public')->copy($originalCvPath, $newCvPath);
                $cvPath = $newCvPath;
            }
        }

        // Créer la candidature
        $application = JobApplication::create([
            'job_offer_id' => $offer->id,
            'user_id' => $user->id,
            'applicant_type' => $applicantType,
            'cv_path' => $cvPath,
            'profile_snapshot' => $profileSnapshot,
            'status' => 'pending',
        ]);

        // Envoyer l'email au Super Admin (sans queue)
        $this->notifySuperAdmin($application);

        session()->flash('success', 'Votre candidature a été soumise avec succès.');
        $this->selectedOfferId = null;
    }

    /**
     * Construire le snapshot du profil selon le type d'utilisateur.
     *
     * @return array<string, mixed>
     */
    protected function buildProfileSnapshot(User $user, bool $isFormateur): array
    {
        $snapshot = [
            'name' => $user->name,
            'first_name' => $user->first_name,
            'email' => $user->email,
            'applied_at' => now()->toIso8601String(),
        ];

        if ($isFormateur && $user->formateurProfile) {
            $profile = $user->formateurProfile->load('certifications', 'organization');

            $snapshot['phone'] = ($profile->phone_country_code ?? '').' '.($profile->phone_number ?? '');
            $snapshot['country'] = $profile->country;
            $snapshot['technical_profile'] = $profile->technical_profile;
            $snapshot['years_of_experience'] = $profile->years_of_experience;
            $snapshot['portfolio_url'] = $profile->portfolio_url;
            $snapshot['organization'] = $profile->organization?->name;
            $snapshot['training_type'] = $profile->training_type;
            $snapshot['certifications'] = $profile->certifications->pluck('name')->toArray();
        }

        return $snapshot;
    }

    /**
     * Notifier le Super Admin d'une nouvelle candidature (sans queue).
     */
    protected function notifySuperAdmin(JobApplication $application): void
    {
        $superAdminRole = Role::where('name', 'super_admin')->first();

        if (! $superAdminRole) {
            return;
        }

        $superAdmins = User::whereHas('roles', function ($query) use ($superAdminRole) {
            $query->where('roles.id', $superAdminRole->id);
        })->get();

        $viewUrl = route('admin.job-application.show', $application);

        foreach ($superAdmins as $admin) {
            try {
                Mail::to($admin->email)->send(new NewJobApplicationMail($application, $viewUrl));
            } catch (\Exception $e) {
                report($e);
            }
        }
    }

    public function render(): View
    {
        /** @var User $user */
        $user = Auth::user();

        // Le super_admin ne doit pas accéder à ce composant
        if ($user->hasRole('super_admin')) {
            abort(403, 'Les super administrateurs doivent gérer les offres via la section administration.');
        }

        $query = JobOffer::active()
            ->with('applications')
            ->latest('published_at');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%'.$this->search.'%')
                    ->orWhere('location', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->contractTypeFilter) {
            $query->where('contract_type', $this->contractTypeFilter);
        }

        if ($this->remotePolicyFilter) {
            $query->where('remote_policy', $this->remotePolicyFilter);
        }

        $jobOffers = $query->paginate(12);

        // Récupérer les IDs des offres auxquelles l'utilisateur a déjà postulé
        $appliedOfferIds = JobApplication::where('user_id', $user->id)
            ->pluck('job_offer_id')
            ->toArray();

        $selectedOffer = $this->selectedOfferId
            ? JobOffer::find($this->selectedOfferId)
            : null;

        $contractTypeOptions = [
            'cdi' => 'CDI',
            'cdd' => 'CDD',
            'stage' => 'Stage',
            'alternance' => 'Alternance',
            'freelance' => 'Freelance',
        ];

        $remotePolicyOptions = [
            'sur_site' => 'Sur site',
            'hybride' => 'Hybride',
            'full_remote' => 'Full remote',
        ];

        return view('livewire.job-offers-list', [
            'jobOffers' => $jobOffers,
            'appliedOfferIds' => $appliedOfferIds,
            'selectedOffer' => $selectedOffer,
            'contractTypeOptions' => $contractTypeOptions,
            'remotePolicyOptions' => $remotePolicyOptions,
            'isFormateur' => $user->hasRole('formateur'),
        ]);
    }
}
