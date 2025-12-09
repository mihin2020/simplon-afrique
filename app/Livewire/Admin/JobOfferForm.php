<?php

namespace App\Livewire\Admin;

use App\Mail\JobOfferPublishedMail;
use App\Models\JobOffer;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class JobOfferForm extends Component
{
    use WithFileUploads;

    public ?JobOffer $jobOffer = null;

    public string $title = '';

    public string $contractType = 'cdi';

    public string $location = '';

    public string $remotePolicy = 'sur_site';

    public string $description = '';

    public string $experienceYears = '';

    public string $minimumEducation = '';

    /** @var array<int, string> */
    public array $requiredSkills = [''];

    public ?string $applicationDeadline = null;

    public ?string $additionalInfo = null;

    public $attachment = null;

    public ?string $existingAttachment = null;

    public bool $isEditing = false;

    public function mount(?string $jobOfferId = null): void
    {
        if ($jobOfferId) {
            $this->jobOffer = JobOffer::findOrFail($jobOfferId);
            $this->isEditing = true;
            $this->fillFromModel();
        }
    }

    protected function fillFromModel(): void
    {
        if (! $this->jobOffer) {
            return;
        }

        $this->title = $this->jobOffer->title;
        $this->contractType = $this->jobOffer->contract_type;
        $this->location = $this->jobOffer->location;
        $this->remotePolicy = $this->jobOffer->remote_policy;
        $this->description = $this->jobOffer->description;
        $this->experienceYears = $this->jobOffer->experience_years;
        $this->minimumEducation = $this->jobOffer->minimum_education;
        $this->requiredSkills = $this->jobOffer->required_skills ?: [''];
        $this->applicationDeadline = $this->jobOffer->application_deadline?->format('Y-m-d');
        $this->additionalInfo = $this->jobOffer->additional_info;
        $this->existingAttachment = $this->jobOffer->attachment_path;
    }

    public function addSkill(): void
    {
        $this->requiredSkills[] = '';
    }

    public function removeSkill(int $index): void
    {
        if (count($this->requiredSkills) > 1) {
            unset($this->requiredSkills[$index]);
            $this->requiredSkills = array_values($this->requiredSkills);
        }
    }

    public function removeAttachment(): void
    {
        if ($this->existingAttachment && $this->jobOffer) {
            Storage::disk('public')->delete($this->existingAttachment);
            $this->jobOffer->update(['attachment_path' => null]);
            $this->existingAttachment = null;
        }
        $this->attachment = null;
    }

    public function saveDraft(): void
    {
        $this->validate();
        $this->save('draft');
    }

    public function publish(): void
    {
        $this->validate();
        $this->save('published');
    }

    protected function save(string $status): void
    {
        // La validation est déjà faite dans saveDraft() et publish()

        $data = [
            'title' => $this->title,
            'contract_type' => $this->contractType,
            'location' => $this->location,
            'remote_policy' => $this->remotePolicy,
            'description' => $this->description,
            'experience_years' => $this->experienceYears,
            'minimum_education' => $this->minimumEducation,
            'required_skills' => array_filter($this->requiredSkills),
            'application_deadline' => $this->applicationDeadline,
            'additional_info' => $this->additionalInfo,
            'status' => $status,
        ];

        // Gérer published_at selon le statut
        if ($status === 'published') {
            // Si on publie, mettre published_at à maintenant si ce n'est pas déjà fait
            if (! $this->jobOffer || ! $this->jobOffer->published_at) {
                $data['published_at'] = now();
            }
        } else {
            // Si on remet en brouillon, réinitialiser published_at
            $data['published_at'] = null;
        }

        // Gérer l'upload du fichier joint
        if ($this->attachment) {
            if ($this->existingAttachment) {
                Storage::disk('public')->delete($this->existingAttachment);
            }

            $path = $this->attachment->store('job-offers/attachments', 'public');
            $data['attachment_path'] = $path;
        }

        if ($this->isEditing && $this->jobOffer) {
            $wasPublished = $this->jobOffer->isPublished();

            // Mettre à jour l'offre
            $this->jobOffer->update($data);

            // Rafraîchir le modèle pour s'assurer que les données sont synchronisées
            $this->jobOffer->refresh();

            // Mettre à jour l'affichage du fichier
            if (isset($data['attachment_path'])) {
                $this->existingAttachment = $data['attachment_path'];
            } elseif ($this->jobOffer->attachment_path) {
                // S'assurer que existingAttachment est à jour même si pas de nouveau fichier
                $this->existingAttachment = $this->jobOffer->attachment_path;
            }

            // Envoyer les emails si c'est une nouvelle publication
            if ($status === 'published' && ! $wasPublished) {
                $this->sendPublicationEmails($this->jobOffer);
            }

            $message = $status === 'published'
                ? 'L\'offre d\'emploi a été publiée avec succès.'
                : 'L\'offre d\'emploi a été enregistrée en brouillon.';
        } else {
            $data['created_by'] = Auth::id();
            $this->jobOffer = JobOffer::create($data);

            // Mettre à jour l'affichage du fichier
            if (isset($data['attachment_path'])) {
                $this->existingAttachment = $data['attachment_path'];
            }

            // Envoyer les emails si publication directe
            if ($status === 'published') {
                $this->sendPublicationEmails($this->jobOffer);
            }

            $message = $status === 'published'
                ? 'L\'offre d\'emploi a été créée et publiée avec succès.'
                : 'L\'offre d\'emploi a été créée en brouillon.';
        }

        // Réinitialiser le champ d'upload
        $this->attachment = null;

        // Si on est en mode édition et qu'on sauvegarde en brouillon, rester sur la page d'édition
        if ($this->isEditing && $status === 'draft') {
            session()->flash('success', $message);
            // Recharger les données depuis la base pour s'assurer qu'elles sont à jour
            $this->jobOffer->refresh();
            $this->fillFromModel();
        } else {
            // Pour la publication ou la création, rediriger vers la page de détail
            session()->flash('success', $message);
            $this->redirect(route('admin.job-offers.show', $this->jobOffer), navigate: true);
        }
    }

    /**
     * Envoyer les emails de notification de publication (sans queue).
     * Optimisé pour envoyer à tous les destinataires en une seule requête SMTP.
     */
    protected function sendPublicationEmails(JobOffer $offer): void
    {
        // Récupérer les rôles admin et formateur
        $roleNames = ['admin', 'formateur'];
        $roleIds = Role::whereIn('name', $roleNames)->pluck('id');

        // Récupérer tous les utilisateurs avec ces rôles
        $recipients = User::whereHas('roles', function ($query) use ($roleIds) {
            $query->whereIn('roles.id', $roleIds);
        })->pluck('email')->toArray();

        if (empty($recipients)) {
            return;
        }

        $applyUrl = route('job-offers.detail', $offer);

        // Envoi optimisé : utiliser le premier destinataire en "to" et les autres en BCC
        // Cela permet d'envoyer un seul email au serveur SMTP au lieu de N emails
        $primaryRecipient = array_shift($recipients);

        try {
            $mail = new JobOfferPublishedMail($offer, $applyUrl);

            if (! empty($recipients)) {
                // Si plusieurs destinataires, utiliser BCC pour les autres
                Mail::to($primaryRecipient)
                    ->bcc($recipients)
                    ->send($mail);
            } else {
                // Un seul destinataire
                Mail::to($primaryRecipient)->send($mail);
            }
        } catch (\Exception $e) {
            // Log l'erreur mais ne bloque pas la publication
            report($e);
        }
    }

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'contractType' => ['required', Rule::in(['cdi', 'cdd', 'stage', 'alternance', 'freelance'])],
            'location' => ['required', 'string', 'max:255'],
            'remotePolicy' => ['required', Rule::in(['sur_site', 'hybride', 'full_remote'])],
            'description' => ['required', 'string', 'min:50'],
            'experienceYears' => ['required', 'string', 'max:100'],
            'minimumEducation' => ['required', 'string', 'max:255'],
            'requiredSkills' => ['required', 'array', 'min:1'],
            'requiredSkills.*' => ['required', 'string', 'max:100'],
            'applicationDeadline' => ['required', 'date', 'after:today'],
            'additionalInfo' => ['nullable', 'string', 'max:5000'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ];
    }

    protected function messages(): array
    {
        return [
            'title.required' => 'Le titre du poste est obligatoire.',
            'title.max' => 'Le titre ne peut pas dépasser 255 caractères.',
            'contractType.required' => 'Le type de contrat est obligatoire.',
            'contractType.in' => 'Le type de contrat sélectionné n\'est pas valide.',
            'location.required' => 'La localisation est obligatoire.',
            'location.max' => 'La localisation ne peut pas dépasser 255 caractères.',
            'remotePolicy.required' => 'La politique de télétravail est obligatoire.',
            'remotePolicy.in' => 'La politique de télétravail sélectionnée n\'est pas valide.',
            'description.required' => 'La description du poste est obligatoire.',
            'description.min' => 'La description doit contenir au moins 50 caractères.',
            'experienceYears.required' => 'Le niveau d\'expérience requis est obligatoire.',
            'minimumEducation.required' => 'Le diplôme/formation minimale est obligatoire.',
            'requiredSkills.required' => 'Au moins une compétence est requise.',
            'requiredSkills.min' => 'Au moins une compétence est requise.',
            'requiredSkills.*.required' => 'La compétence ne peut pas être vide.',
            'requiredSkills.*.max' => 'Chaque compétence ne peut pas dépasser 100 caractères.',
            'applicationDeadline.required' => 'La date limite de candidature est obligatoire.',
            'applicationDeadline.date' => 'La date limite de candidature doit être une date valide.',
            'applicationDeadline.after' => 'La date limite de candidature doit être postérieure à aujourd\'hui.',
            'additionalInfo.max' => 'Les informations complémentaires ne peuvent pas dépasser 5000 caractères.',
            'attachment.file' => 'Le fichier joint doit être un fichier valide.',
            'attachment.mimes' => 'Le fichier joint doit être au format PDF, JPG, JPEG ou PNG.',
            'attachment.max' => 'Le fichier joint ne peut pas dépasser 10 Mo.',
        ];
    }

    public function render(): View
    {
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

        $experienceOptions = [
            '0-2 ans' => '0-2 ans',
            '2-5 ans' => '2-5 ans',
            '5-10 ans' => '5-10 ans',
            '+10 ans' => '+10 ans',
        ];

        $educationOptions = [
            'Bac' => 'Bac',
            'Bac+2' => 'Bac+2',
            'Bac+3' => 'Bac+3 (Licence)',
            'Bac+5' => 'Bac+5 (Master)',
            'Doctorat' => 'Doctorat',
            'Autre' => 'Autre',
        ];

        return view('livewire.admin.job-offer-form', [
            'contractTypeOptions' => $contractTypeOptions,
            'remotePolicyOptions' => $remotePolicyOptions,
            'experienceOptions' => $experienceOptions,
            'educationOptions' => $educationOptions,
        ]);
    }
}
