<?php

namespace App\Livewire\Admin;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

class Profile extends Component
{
    public $firstName = '';

    public $lastName = '';

    public $email = '';

    public $currentPassword = '';

    public $password = '';

    public $passwordConfirmation = '';

    public function mount(): void
    {
        $user = Auth::user();
        $this->firstName = $user->first_name ?? '';
        $this->lastName = $user->name ?? '';
        $this->email = $user->email ?? '';
    }

    public function updateProfile(): void
    {
        $user = Auth::user();

        $this->validate([
            'firstName' => ['required', 'string', 'max:255'],
            'lastName' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
        ], [
            'firstName.required' => 'Le prénom est requis.',
            'lastName.required' => 'Le nom est requis.',
            'email.required' => 'L\'email est requis.',
            'email.email' => 'L\'email doit être une adresse email valide.',
            'email.unique' => 'Cet email est déjà utilisé par un autre utilisateur.',
        ]);

        $user->update([
            'first_name' => trim($this->firstName),
            'name' => trim($this->lastName),
            'email' => trim($this->email),
        ]);

        session()->flash('success', 'Vos informations ont été mises à jour avec succès.');
    }

    public function updatePassword(): void
    {
        $this->validate([
            'currentPassword' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'currentPassword.required' => 'Le mot de passe actuel est requis.',
            'password.required' => 'Le nouveau mot de passe est requis.',
            'password.min' => 'Le nouveau mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ]);

        $user = Auth::user();

        // Vérifier que le mot de passe actuel est correct
        if (! Hash::check($this->currentPassword, $user->password)) {
            $this->addError('currentPassword', 'Le mot de passe actuel est incorrect.');

            return;
        }

        // Mettre à jour le mot de passe
        $user->update([
            'password' => Hash::make($this->password),
        ]);

        // Réinitialiser les champs
        $this->reset(['currentPassword', 'password', 'passwordConfirmation']);

        session()->flash('success', 'Votre mot de passe a été modifié avec succès.');
    }

    public function render(): View
    {
        $user = Auth::user();

        return view('livewire.admin.profile', [
            'user' => $user,
        ]);
    }
}
