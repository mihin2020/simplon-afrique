<?php

namespace App\Livewire\Formateur;

use App\Data\CountriesData;
use App\Models\CertificationTag;
use App\Models\FormateurProfile;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class Profile extends Component
{
    use WithFileUploads;

    public $name = '';

    public $firstName = '';

    public $email = '';

    public $photo;

    public $photoPreview;

    public $cv;

    public $cvPreview;

    public $phoneCountryCode = '+33';

    public $phoneNumber;

    public $country;

    public $technicalProfile;

    public $yearsOfExperience;

    public $portfolioUrl;

    public $selectedCertifications = [];

    public $certificationSearch = '';

    public $availableCertifications = [];

    public function mount(): void
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return;
        }

        // Charger les données de l'utilisateur
        $user->refresh();

        $this->name = $user->name ?? '';
        $this->firstName = $user->first_name ?? '';
        $this->email = $user->email ?? '';

        $profile = $user->formateurProfile;

        if ($profile) {
            $this->phoneCountryCode = $profile->phone_country_code ?? '+33';
            $this->phoneNumber = $profile->phone_number;
            $this->country = $profile->country;
            $this->technicalProfile = $profile->technical_profile;
            $this->yearsOfExperience = $profile->years_of_experience;
            $this->portfolioUrl = $profile->portfolio_url;
            $this->photoPreview = $profile->photo_path ? Storage::url($profile->photo_path) : null;
            $this->cvPreview = $profile->cv_path ? basename($profile->cv_path) : null;
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
        $this->validateOnly('cv');
        $this->cvPreview = $this->cv?->getClientOriginalName();
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
        $this->validate([
            'certificationSearch' => ['required', 'string', 'max:255', 'unique:certifications_tags,name'],
        ], [
            'certificationSearch.unique' => 'Cette certification existe déjà.',
        ]);

        $certification = CertificationTag::create([
            'name' => trim($this->certificationSearch),
        ]);

        $this->selectedCertifications[] = $certification->id;
        $this->certificationSearch = '';
        $this->loadAvailableCertifications();

        session()->flash('message', 'Certification ajoutée avec succès.');
    }

    public function save(): void
    {
        $this->validate();

        $user = Auth::user();

        if (! $user instanceof User) {
            return;
        }

        $user->name = $this->name;
        $user->first_name = $this->firstName;
        $user->email = $this->email;
        $user->save();

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

        if ($this->cv) {
            if ($profile && $profile->cv_path) {
                Storage::disk('public')->delete($profile->cv_path);
            }

            $cvPath = $this->cv->store('formateurs/cv', 'public');
            $data['cv_path'] = $cvPath;
            $this->cvPreview = $this->cv->getClientOriginalName();
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

    public function removeCv(): void
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return;
        }

        $profile = $user->formateurProfile;

        if (! $profile || ! $profile->cv_path) {
            return;
        }

        Storage::disk('public')->delete($profile->cv_path);

        $profile->update([
            'cv_path' => null,
        ]);

        $this->cv = null;
        $this->cvPreview = null;

        session()->flash('message', 'CV supprimé avec succès.');
    }

    protected function rules(): array
    {
        $userId = Auth::id();

        return [
            'name' => ['required', 'string', 'max:255'],
            'firstName' => ['nullable', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'phoneCountryCode' => ['nullable', 'string', 'max:10'],
            'phoneNumber' => ['nullable', 'string', 'max:30'],
            'country' => ['nullable', 'string', 'max:255'],
            'technicalProfile' => ['nullable', 'string', 'max:255'],
            'yearsOfExperience' => ['nullable', 'string', 'max:20'],
            'portfolioUrl' => ['nullable', 'url', 'max:255'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'cv' => ['nullable', 'mimes:pdf', 'max:5120'],
            'selectedCertifications' => ['array'],
        ];
    }

    public function getExperienceOptions(): array
    {
        return [
            'moins_de_2_ans' => 'Moins de 2 ans',
            'entre_2_et_5_ans' => 'Entre 2 et 5 ans',
            'plus_de_5_ans' => 'Plus de 5 ans',
        ];
    }

    public function render(): View
    {
        return view('livewire.formateur.profile', [
            'selectedCertificationsList' => CertificationTag::whereIn('id', $this->selectedCertifications)->get(),
            'countries' => CountriesData::getCountries(),
            'phoneCountryCodes' => CountriesData::getPhoneCountryCodes(),
            'experienceOptions' => $this->getExperienceOptions(),
        ]);
    }
}
