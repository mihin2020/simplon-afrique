<?php

namespace App\Livewire\Formateur;

use App\Models\Candidature;
use App\Models\CandidatureStep;
use App\Models\LabellisationStep;
use App\Models\Role;
use App\Models\User;
use App\Notifications\FormateurCandidatureSubmittedNotification;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateCandidature extends Component
{
    use WithFileUploads;

    public $motivationLetter;

    public $motivationLetterPreview;

    public $additionalAttachments = [];

    public $attachmentPreviews = [];

    protected $rules = [
        'motivationLetter' => ['required', 'file', 'mimes:pdf', 'max:5120'],
        'additionalAttachments.*' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
    ];

    protected $messages = [
        'motivationLetter.required' => 'La lettre de motivation est obligatoire.',
        'motivationLetter.mimes' => 'La lettre de motivation doit être un fichier PDF.',
        'motivationLetter.max' => 'La lettre de motivation ne doit pas dépasser 5 Mo.',
        'additionalAttachments.*.mimes' => 'Les pièces jointes doivent être des fichiers PDF, DOC ou DOCX.',
        'additionalAttachments.*.max' => 'Chaque pièce jointe ne doit pas dépasser 5 Mo.',
    ];

    public function mount(): void
    {
        // La vérification de candidature en cours est faite dans render()
    }

    public function updatedMotivationLetter(): void
    {
        $this->validateOnly('motivationLetter');
        $this->motivationLetterPreview = $this->motivationLetter->getClientOriginalName();
    }

    public function addAttachment(): void
    {
        $this->additionalAttachments[] = null;
    }

    public function removeAttachment(int $index): void
    {
        unset($this->additionalAttachments[$index]);
        unset($this->attachmentPreviews[$index]);
        $this->additionalAttachments = array_values($this->additionalAttachments);
        $this->attachmentPreviews = array_values($this->attachmentPreviews);
    }

    public function updatedAdditionalAttachments($value, int $index): void
    {
        if (isset($this->additionalAttachments[$index]) && $this->additionalAttachments[$index]) {
            $this->validateOnly("additionalAttachments.{$index}");
            $this->attachmentPreviews[$index] = $this->additionalAttachments[$index]->getClientOriginalName();
        }
    }

    public function submit(): void
    {
        $this->validate();

        $user = auth()->user();

        // Vérifier si l'utilisateur a un profil complété
        if (! $user->formateurProfile) {
            session()->flash('error', 'Veuillez d\'abord compléter votre profil avant de déposer une candidature.');

            return;
        }

        // Vérifier si l'utilisateur a un CV dans son profil
        if (! $user->formateurProfile->cv_path) {
            session()->flash('error', 'Veuillez d\'abord téléverser votre CV dans votre profil avant de déposer une candidature.');

            return;
        }

        // Vérifier si l'utilisateur a déjà une candidature en cours
        $existingCandidature = $user->candidatures()
            ->whereIn('status', ['draft', 'submitted', 'in_review'])
            ->latest()
            ->first();

        if ($existingCandidature) {
            session()->flash('error', 'Vous avez déjà une candidature en cours. Vous ne pouvez pas en déposer une nouvelle.');

            return;
        }

        // Obtenir la première étape de labellisation
        $firstStep = LabellisationStep::orderBy('display_order')->first();

        if (! $firstStep) {
            session()->flash('error', 'Erreur : aucune étape de labellisation n\'est configurée.');

            return;
        }

        // Copier le CV du profil vers la candidature (version figée)
        $profileCvPath = $user->formateurProfile->cv_path;
        $cvExtension = pathinfo($profileCvPath, PATHINFO_EXTENSION);
        $cvNewName = 'candidatures/cv/'.uniqid().'_'.time().'.'.$cvExtension;
        \Illuminate\Support\Facades\Storage::disk('public')->copy($profileCvPath, $cvNewName);
        $cvPath = $cvNewName;

        // Stocker la lettre de motivation
        $motivationLetterPath = $this->motivationLetter->store('candidatures/motivation-letters', 'public');

        $attachments = [];
        foreach ($this->additionalAttachments as $index => $attachment) {
            if ($attachment) {
                $path = $attachment->store('candidatures/attachments', 'public');
                $attachments[] = [
                    'name' => $attachment->getClientOriginalName(),
                    'path' => $path,
                ];
            }
        }

        // Récupérer le portfolio depuis le profil si disponible
        $portfolioUrl = $user->formateurProfile?->portfolio_url;

        // Créer la candidature (sans badge_id - sera attribué automatiquement selon la moyenne)
        $candidature = Candidature::create([
            'user_id' => $user->id,
            'badge_id' => null,
            'current_step_id' => $firstStep->id,
            'status' => 'submitted',
            'cv_path' => $cvPath,
            'motivation_letter_path' => $motivationLetterPath,
            'portfolio_url' => $portfolioUrl,
            'attachments' => ! empty($attachments) ? $attachments : null,
        ]);

        // Créer le premier step de candidature
        CandidatureStep::create([
            'candidature_id' => $candidature->id,
            'labellisation_step_id' => $firstStep->id,
            'status' => 'in_progress',
        ]);

        // Envoyer une notification à tous les super admins
        $superAdminRole = Role::where('name', 'super_admin')->first();
        if ($superAdminRole) {
            $superAdmins = User::whereHas('roles', function ($query) use ($superAdminRole) {
                $query->where('roles.id', $superAdminRole->id);
            })->get();

            foreach ($superAdmins as $superAdmin) {
                $superAdmin->notify(new FormateurCandidatureSubmittedNotification($candidature));
            }
        }

        // Réinitialiser le formulaire
        $this->reset(['motivationLetter', 'motivationLetterPreview', 'additionalAttachments', 'attachmentPreviews']);

        session()->flash('success', 'Votre candidature a été déposée avec succès ! Elle est maintenant en cours d\'examen.');

        // Rediriger vers la page des candidatures
        $this->redirect(route('formateur.candidatures'), navigate: true);
    }

    public function render()
    {
        $user = auth()->user();

        // Vérifier si l'utilisateur a déjà une candidature en cours
        $hasActiveCandidature = $user->candidatures()
            ->whereIn('status', ['draft', 'submitted', 'in_review'])
            ->exists();

        return view('livewire.formateur.create-candidature', [
            'hasActiveCandidature' => $hasActiveCandidature,
        ]);
    }
}
