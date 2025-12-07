<?php

namespace App\Livewire\Formateur;

use App\Data\CountriesData;
use App\Models\CertificationTag;
use App\Models\FormateurProfile;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class Profile extends Component
{
    use WithFileUploads;

    public $photo;

    public $photoPreview;

    public $name;

    public $firstName;

    public $email;

    public $phoneCountryCode = '+33';

    public $phoneNumber;

    public $country;

    public $technicalProfile;

    public $yearsOfExperience;

    public $portfolioUrl;

    public $cv;

    public $cvPreview;

    public $selectedCertifications = [];

    public $certificationSearch = '';

    public $availableCertifications = [];

    protected $rules = [
        'name' => ['required', 'string', 'max:255'],
        'firstName' => ['nullable', 'string', 'max:255'],
        'email' => ['required', 'email', 'max:255'],
        'phoneCountryCode' => ['nullable', 'string', 'max:10'],
        'phoneNumber' => ['nullable', 'string', 'max:30'],
        'country' => ['nullable', 'string', 'max:255'],
        'technicalProfile' => ['nullable', 'string', 'max:255'],
        'yearsOfExperience' => ['nullable', 'string', 'max:20'],
        'portfolioUrl' => ['nullable', 'url', 'max:255'],
        'photo' => ['nullable', 'image', 'max:2048'],
        'selectedCertifications' => ['array'],
    ];

    public function mount(): void
    {
        $user = auth()->user();
        $profile = $user->formateurProfile;

        // Récupérer les informations de l'utilisateur
        $this->name = $user->name;
        $this->firstName = $user->first_name ?? '';
        $this->email = $user->email;

        if ($profile) {
            $this->phoneCountryCode = $profile->phone_country_code ?? '+33';
            $this->phoneNumber = $profile->phone_number;
            $this->country = $profile->country;
            $this->technicalProfile = $profile->technical_profile;
            $this->yearsOfExperience = $profile->years_of_experience;
            $this->portfolioUrl = $profile->portfolio_url;
            $this->photoPreview = $profile->photo_path ? Storage::url($profile->photo_path) : null;
            $this->cvPreview = $profile->cv_path ? $this->extractOriginalCvName($profile->cv_path) : null;
            $this->selectedCertifications = $profile->certifications->pluck('id')->toArray();
        } else {
            // Valeurs par défaut si pas de profil
            $this->phoneCountryCode = '+33';
            $this->country = 'France';
        }

        $this->loadAvailableCertifications();
    }

    public function updatedPhoto(): void
    {
        $this->validateOnly('photo');
        $this->photoPreview = $this->photo->temporaryUrl();
    }

    public function updatedCv(): void
    {
        $this->validate([
            'cv' => ['required', 'file', 'mimes:pdf', 'max:5120'],
        ], [
            'cv.mimes' => 'Le CV doit être un fichier PDF.',
            'cv.max' => 'Le CV ne doit pas dépasser 5 Mo.',
        ]);
        $this->cvPreview = $this->cv->getClientOriginalName();
    }

    public function removeCv(): void
    {
        $user = auth()->user();
        $profile = $user->formateurProfile;

        if ($profile && $profile->cv_path) {
            Storage::disk('public')->delete($profile->cv_path);
            $profile->update(['cv_path' => null]);
        }

        $this->cv = null;
        $this->cvPreview = null;

        session()->flash('success', 'CV supprimé avec succès.');
    }

    public function updatedCertificationSearch(): void
    {
        $this->loadAvailableCertifications();
    }

    public function loadAvailableCertifications(): void
    {
        $query = CertificationTag::query();

        if ($this->certificationSearch) {
            $query->where('name', 'like', '%'.$this->certificationSearch.'%');
        }

        $this->availableCertifications = $query->orderBy('name')->limit(10)->get();
    }

    public function toggleCertification(string $certificationId): void
    {
        if (in_array($certificationId, $this->selectedCertifications)) {
            $this->selectedCertifications = array_values(array_diff($this->selectedCertifications, [$certificationId]));
        } else {
            $this->selectedCertifications[] = $certificationId;
        }
    }

    public function addNewCertification(): void
    {
        $searchTerm = trim($this->certificationSearch);

        if (empty($searchTerm)) {
            return;
        }

        // Vérifier si la certification existe déjà dans la base de données
        $existingCertification = CertificationTag::where('name', $searchTerm)->first();

        if ($existingCertification) {
            // Si elle existe, l'ajouter aux sélections si pas déjà sélectionnée
            if (! in_array($existingCertification->id, $this->selectedCertifications)) {
                $this->selectedCertifications[] = $existingCertification->id;
            }
        } else {
            // Créer la nouvelle certification
            $this->validate([
                'certificationSearch' => ['required', 'string', 'max:255', 'unique:certifications_tags,name'],
            ], [
                'certificationSearch.unique' => 'Cette certification existe déjà.',
            ]);

            $certification = CertificationTag::create([
                'name' => $searchTerm,
            ]);

            if (! in_array($certification->id, $this->selectedCertifications)) {
                $this->selectedCertifications[] = $certification->id;
            }
        }

        // Vider le champ de recherche et recharger
        $this->certificationSearch = '';
        $this->loadAvailableCertifications();

        session()->flash('message', 'Certification ajoutée avec succès.');
    }

    public function save(): void
    {
        // Validation personnalisée pour l'email unique (sauf pour l'utilisateur actuel)
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'firstName' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.auth()->id()],
            'phoneCountryCode' => ['nullable', 'string', 'max:10'],
            'phoneNumber' => ['nullable', 'string', 'max:30'],
            'country' => ['nullable', 'string', 'max:255'],
            'technicalProfile' => ['nullable', 'string', 'max:255'],
            'yearsOfExperience' => ['nullable', 'string', 'max:20'],
            'portfolioUrl' => ['nullable', 'url', 'max:255'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'cv' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
            'selectedCertifications' => ['array'],
        ]);

        $user = auth()->user();

        // Mettre à jour les informations de l'utilisateur
        $user->update([
            'name' => $this->name,
            'first_name' => $this->firstName,
            'email' => $this->email,
        ]);

        $profile = $user->formateurProfile;

        $data = [
            'phone_country_code' => $this->phoneCountryCode,
            'phone_number' => $this->phoneNumber,
            'country' => $this->country,
            'technical_profile' => $this->technicalProfile,
            'years_of_experience' => $this->yearsOfExperience,
            'portfolio_url' => $this->portfolioUrl,
        ];

        // Gérer l'upload de la photo
        if ($this->photo) {
            // Supprimer l'ancienne photo si elle existe
            if ($profile && $profile->photo_path) {
                Storage::disk('public')->delete($profile->photo_path);
            }

            // Stocker la nouvelle photo
            $path = $this->photo->store('formateurs/photos', 'public');
            $data['photo_path'] = $path;
            $this->photoPreview = Storage::url($path);
        }

        // Gérer l'upload du CV
        if ($this->cv) {
            // Supprimer l'ancien CV si il existe
            if ($profile && $profile->cv_path) {
                Storage::disk('public')->delete($profile->cv_path);
            }

            // Stocker le nouveau CV avec son nom original
            $cvOriginalName = $this->cv->getClientOriginalName();
            // Nettoyer le nom du fichier pour éviter les problèmes
            $cvSafeName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $cvOriginalName);
            // Ajouter un timestamp pour éviter les doublons
            $cvFileName = time().'_'.$cvSafeName;
            $cvPath = $this->cv->storeAs('formateurs/cv', $cvFileName, 'public');
            $data['cv_path'] = $cvPath;
            $this->cvPreview = $cvOriginalName;
        }

        if ($profile) {
            $profile->update($data);
        } else {
            $data['user_id'] = $user->id;
            $profile = FormateurProfile::create($data);
        }

        // Synchroniser les certifications
        $profile->certifications()->sync($this->selectedCertifications);

        $this->photo = null;
        $this->cv = null;
        session()->flash('success', 'Profil mis à jour avec succès.');
    }

    public function getExperienceOptions(): array
    {
        return [
            'moins_de_2_ans' => 'Moins de 2 ans',
            'entre_2_et_5_ans' => 'Entre 2 et 5 ans',
            'plus_de_5_ans' => 'Plus de 5 ans',
        ];
    }

    /**
     * Extrait le nom original du CV depuis le chemin stocké.
     * Le format est : timestamp_nom_original.pdf
     */
    private function extractOriginalCvName(string $cvPath): string
    {
        $filename = basename($cvPath);
        // Retirer le timestamp au début (format: 1234567890_nom_fichier.pdf)
        if (preg_match('/^\d+_(.+)$/', $filename, $matches)) {
            return $matches[1];
        }

        return $filename;
    }

    public function render()
    {
        return view('livewire.formateur.profile', [
            'selectedCertificationsList' => CertificationTag::whereIn('id', $this->selectedCertifications)->get(),
            'countries' => CountriesData::getCountries(),
            'phoneCountryCodes' => CountriesData::getPhoneCountryCodes(),
            'experienceOptions' => $this->getExperienceOptions(),
        ]);
    }
}


        session()->flash('message', 'Certification ajoutée avec succès.');
    }

    public function save(): void
    {
        // Validation personnalisée pour l'email unique (sauf pour l'utilisateur actuel)
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'firstName' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.auth()->id()],
            'phoneCountryCode' => ['nullable', 'string', 'max:10'],
            'phoneNumber' => ['nullable', 'string', 'max:30'],
            'country' => ['nullable', 'string', 'max:255'],
            'technicalProfile' => ['nullable', 'string', 'max:255'],
            'yearsOfExperience' => ['nullable', 'string', 'max:20'],
            'portfolioUrl' => ['nullable', 'url', 'max:255'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'cv' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
            'selectedCertifications' => ['array'],
        ]);

        $user = auth()->user();

        // Mettre à jour les informations de l'utilisateur
        $user->update([
            'name' => $this->name,
            'first_name' => $this->firstName,
            'email' => $this->email,
        ]);

        $profile = $user->formateurProfile;

        $data = [
            'phone_country_code' => $this->phoneCountryCode,
            'phone_number' => $this->phoneNumber,
            'country' => $this->country,
            'technical_profile' => $this->technicalProfile,
            'years_of_experience' => $this->yearsOfExperience,
            'portfolio_url' => $this->portfolioUrl,
        ];

        // Gérer l'upload de la photo
        if ($this->photo) {
            // Supprimer l'ancienne photo si elle existe
            if ($profile && $profile->photo_path) {
                Storage::disk('public')->delete($profile->photo_path);
            }

            // Stocker la nouvelle photo
            $path = $this->photo->store('formateurs/photos', 'public');
            $data['photo_path'] = $path;
            $this->photoPreview = Storage::url($path);
        }

        // Gérer l'upload du CV
        if ($this->cv) {
            // Supprimer l'ancien CV si il existe
            if ($profile && $profile->cv_path) {
                Storage::disk('public')->delete($profile->cv_path);
            }

            // Stocker le nouveau CV avec son nom original
            $cvOriginalName = $this->cv->getClientOriginalName();
            // Nettoyer le nom du fichier pour éviter les problèmes
            $cvSafeName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $cvOriginalName);
            // Ajouter un timestamp pour éviter les doublons
            $cvFileName = time().'_'.$cvSafeName;
            $cvPath = $this->cv->storeAs('formateurs/cv', $cvFileName, 'public');
            $data['cv_path'] = $cvPath;
            $this->cvPreview = $cvOriginalName;
        }

        if ($profile) {
            $profile->update($data);
        } else {
            $data['user_id'] = $user->id;
            $profile = FormateurProfile::create($data);
        }

        // Synchroniser les certifications
        $profile->certifications()->sync($this->selectedCertifications);

        $this->photo = null;
        $this->cv = null;
        session()->flash('success', 'Profil mis à jour avec succès.');
    }

    public function getExperienceOptions(): array
    {
        return [
            'moins_de_2_ans' => 'Moins de 2 ans',
            'entre_2_et_5_ans' => 'Entre 2 et 5 ans',
            'plus_de_5_ans' => 'Plus de 5 ans',
        ];
    }

    /**
     * Extrait le nom original du CV depuis le chemin stocké.
     * Le format est : timestamp_nom_original.pdf
     */
    private function extractOriginalCvName(string $cvPath): string
    {
        $filename = basename($cvPath);
        // Retirer le timestamp au début (format: 1234567890_nom_fichier.pdf)
        if (preg_match('/^\d+_(.+)$/', $filename, $matches)) {
            return $matches[1];
        }

        return $filename;
    }

    public function render()
    {
        return view('livewire.formateur.profile', [
            'selectedCertificationsList' => CertificationTag::whereIn('id', $this->selectedCertifications)->get(),
            'countries' => CountriesData::getCountries(),
            'phoneCountryCodes' => CountriesData::getPhoneCountryCodes(),
            'experienceOptions' => $this->getExperienceOptions(),
        ]);
    }
}
