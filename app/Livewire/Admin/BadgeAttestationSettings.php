<?php

namespace App\Livewire\Admin;

use App\Models\AttestationSetting;
use App\Models\Badge;
use App\Models\BadgeConfiguration;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class BadgeAttestationSettings extends Component
{
    use WithFileUploads;

    // Badge images
    public $badgeImages = [];

    public $tempBadgeImages = [];

    // Attestation settings
    public $directorName = '';

    public $directorTitle = '';

    public $organizationName = '';

    public $attestationText = '';

    public $signature;

    public $signaturePreview;

    public $logo;

    public $logoPreview;

    protected $rules = [
        'directorName' => ['required', 'string', 'max:255'],
        'directorTitle' => ['required', 'string', 'max:255'],
        'organizationName' => ['required', 'string', 'max:255'],
        'attestationText' => ['required', 'string'],
        'signature' => ['nullable', 'image', 'max:2048'],
        'logo' => ['nullable', 'image', 'max:2048'],
        'tempBadgeImages.*' => ['nullable', 'image', 'max:2048'],
    ];

    protected $messages = [
        'directorName.required' => 'Le nom du directeur est obligatoire.',
        'directorTitle.required' => 'Le titre du directeur est obligatoire.',
        'organizationName.required' => 'Le nom de l\'organisation est obligatoire.',
        'attestationText.required' => 'Le texte de l\'attestation est obligatoire.',
        'signature.image' => 'La signature doit être une image.',
        'signature.max' => 'La signature ne doit pas dépasser 2 Mo.',
        'logo.image' => 'Le logo doit être une image.',
        'logo.max' => 'Le logo ne doit pas dépasser 2 Mo.',
    ];

    public function mount(): void
    {
        $settings = AttestationSetting::getSettings();

        $this->directorName = $settings->director_name ?? '';
        $this->directorTitle = $settings->director_title ?? '';
        $this->organizationName = $settings->organization_name ?? 'Simplon Africa';
        $this->attestationText = $settings->attestation_text ?? '';
        $this->signaturePreview = $settings->signature_path ? Storage::disk('public')->url($settings->signature_path) : null;
        $this->logoPreview = $settings->logo_path ? Storage::disk('public')->url($settings->logo_path) : null;

        // Charger les images de badges existantes
        $badges = Badge::with('configuration')->orderBy('min_score')->get();
        foreach ($badges as $badge) {
            $this->badgeImages[$badge->id] = $badge->configuration?->image_path
                ? Storage::disk('public')->url($badge->configuration->image_path)
                : null;
        }
    }

    public function updatedSignature(): void
    {
        $this->validate(['signature' => ['image', 'max:2048']]);
        $this->signaturePreview = $this->signature->temporaryUrl();
    }

    public function updatedLogo(): void
    {
        $this->validate(['logo' => ['image', 'max:2048']]);
        $this->logoPreview = $this->logo->temporaryUrl();
    }

    public function updatedTempBadgeImages($value, $badgeId): void
    {
        $this->validate(["tempBadgeImages.{$badgeId}" => ['image', 'max:2048']]);
    }

    public function saveBadgeImage(string $badgeId): void
    {
        if (! isset($this->tempBadgeImages[$badgeId])) {
            return;
        }

        $file = $this->tempBadgeImages[$badgeId];
        $path = $file->store('badges', 'public');

        $config = BadgeConfiguration::updateOrCreate(
            ['badge_id' => $badgeId],
            ['image_path' => $path]
        );

        $this->badgeImages[$badgeId] = Storage::disk('public')->url($path);
        unset($this->tempBadgeImages[$badgeId]);

        session()->flash('success', 'Image du badge mise à jour avec succès.');
    }

    public function removeBadgeImage(string $badgeId): void
    {
        $config = BadgeConfiguration::where('badge_id', $badgeId)->first();

        if ($config && $config->image_path) {
            Storage::disk('public')->delete($config->image_path);
            $config->update(['image_path' => null]);
        }

        $this->badgeImages[$badgeId] = null;
        session()->flash('success', 'Image du badge supprimée.');
    }

    public function saveAttestationSettings(): void
    {
        $this->validate([
            'directorName' => ['required', 'string', 'max:255'],
            'directorTitle' => ['required', 'string', 'max:255'],
            'organizationName' => ['required', 'string', 'max:255'],
            'attestationText' => ['required', 'string'],
        ]);

        $settings = AttestationSetting::getSettings();

        $data = [
            'director_name' => $this->directorName,
            'director_title' => $this->directorTitle,
            'organization_name' => $this->organizationName,
            'attestation_text' => $this->attestationText,
        ];

        // Upload signature
        if ($this->signature) {
            if ($settings->signature_path) {
                Storage::disk('public')->delete($settings->signature_path);
            }
            $data['signature_path'] = $this->signature->store('attestation', 'public');
            $this->signaturePreview = Storage::disk('public')->url($data['signature_path']);
            $this->signature = null;
        }

        // Upload logo
        if ($this->logo) {
            if ($settings->logo_path) {
                Storage::disk('public')->delete($settings->logo_path);
            }
            $data['logo_path'] = $this->logo->store('attestation', 'public');
            $this->logoPreview = Storage::disk('public')->url($data['logo_path']);
            $this->logo = null;
        }

        $settings->update($data);

        session()->flash('success', 'Paramètres de l\'attestation mis à jour avec succès.');
    }

    public function removeSignature(): void
    {
        $settings = AttestationSetting::getSettings();

        if ($settings->signature_path) {
            Storage::disk('public')->delete($settings->signature_path);
            $settings->update(['signature_path' => null]);
        }

        $this->signaturePreview = null;
        session()->flash('success', 'Signature supprimée.');
    }

    public function removeLogo(): void
    {
        $settings = AttestationSetting::getSettings();

        if ($settings->logo_path) {
            Storage::disk('public')->delete($settings->logo_path);
            $settings->update(['logo_path' => null]);
        }

        $this->logoPreview = null;
        session()->flash('success', 'Logo supprimé.');
    }

    public function render()
    {
        $badges = Badge::with('configuration')->orderBy('min_score')->get();
        
        // Déterminer le badge à afficher dans l'aperçu (utiliser un score exemple de 15.0 pour badge intermédiaire)
        $previewBadge = $this->getBadgeForPreview(15.0);

        return view('livewire.admin.badge-attestation-settings', [
            'badges' => $badges,
            'previewBadge' => $previewBadge,
        ]);
    }
    
    /**
     * Détermine le badge à afficher dans l'aperçu basé sur un score exemple.
     */
    private function getBadgeForPreview(float $exampleScore): ?Badge
    {
        return Badge::where('min_score', '<=', $exampleScore)
            ->where('max_score', '>=', $exampleScore)
            ->orderBy('min_score', 'desc')
            ->first();
    }
}
