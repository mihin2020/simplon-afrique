<?php

namespace App\Livewire\Admin;

use App\Models\Jury;
use App\Models\JuryMember;
use App\Models\User;
use Livewire\Component;

class JuryAddMember extends Component
{
    public string $juryId;

    public ?Jury $jury = null;

    public string $selectedUserId = '';

    public string $selectedRole = 'referent_pedagogique';

    /**
     * Identifiant du membre sélectionné comme président.
     */
    public string $selectedPresidentId = '';

    public string $message = '';

    public string $messageType = '';

    public function mount(string $juryId): void
    {
        $this->juryId = $juryId;
        $this->jury = Jury::with(['members.user'])->findOrFail($juryId);

        // Pré-sélectionner le président actuel s'il existe
        $currentPresident = $this->jury->members->firstWhere('is_president', true);
        $this->selectedPresidentId = $currentPresident?->id ?? '';
    }

    private function canManage(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        $user->loadMissing('roles');

        return $user->roles->contains('name', 'super_admin');
    }

    public function storeMember(): void
    {
        $this->message = '';
        $this->messageType = '';

        if (! $this->canManage()) {
            $this->message = 'Vous n\'avez pas les droits pour modifier la composition de ce jury.';
            $this->messageType = 'error';

            return;
        }

        if (! $this->jury) {
            $this->message = 'Le jury n\'a pas été trouvé.';
            $this->messageType = 'error';

            return;
        }

        if ($this->selectedUserId === '') {
            $this->message = 'Veuillez sélectionner un utilisateur.';
            $this->messageType = 'error';

            return;
        }

        // Vérifier si déjà membre de ce jury
        $alreadyMember = JuryMember::where('jury_id', $this->jury->id)
            ->where('user_id', $this->selectedUserId)
            ->exists();

        if ($alreadyMember) {
            $this->message = 'Cet utilisateur est déjà membre de ce jury.';
            $this->messageType = 'error';

            return;
        }

        // Valider le rôle
        $validRoles = ['referent_pedagogique', 'directeur_pedagogique', 'formateur_senior'];
        $role = in_array($this->selectedRole, $validRoles) ? $this->selectedRole : 'referent_pedagogique';

        // Créer le membre
        JuryMember::create([
            'jury_id' => $this->jury->id,
            'user_id' => $this->selectedUserId,
            'role' => $role,
            'is_president' => false,
        ]);

        // Recharger le jury
        $this->jury = Jury::with(['members.user'])->findOrFail($this->juryId);

        $roleLabels = [
            'referent_pedagogique' => 'Référent Pédagogique',
            'directeur_pedagogique' => 'Directeur Pédagogique',
            'formateur_senior' => 'Formateur Senior',
        ];

        $this->message = 'Membre ajouté au jury avec le profil "'.($roleLabels[$role] ?? $role).'".';
        $this->messageType = 'success';

        // Réinitialiser
        $this->selectedUserId = '';
        $this->selectedRole = 'referent_pedagogique';
    }

    public function updateMemberRole(string $memberId, string $role): void
    {
        if (! $this->canManage()) {
            return;
        }

        $validRoles = ['referent_pedagogique', 'directeur_pedagogique', 'formateur_senior'];
        if (! in_array($role, $validRoles)) {
            return;
        }

        $member = JuryMember::find($memberId);
        if ($member && $member->jury_id === $this->jury->id) {
            $member->update(['role' => $role]);
            $this->jury = Jury::with(['members.user'])->findOrFail($this->juryId);
            $this->message = 'Profil mis à jour.';
            $this->messageType = 'success';
        }
    }

    public function togglePresident(string $memberId): void
    {
        $this->message = '';
        $this->messageType = '';

        if (! $this->canManage() || ! $this->jury) {
            $this->message = 'Action non autorisée.';
            $this->messageType = 'error';

            return;
        }

        // Vérifier que le membre appartient bien à ce jury
        $member = JuryMember::with('user')
            ->where('id', $memberId)
            ->where('jury_id', $this->jury->id)
            ->first();

        if (! $member) {
            $this->message = 'Membre non trouvé.';
            $this->messageType = 'error';

            return;
        }

        // Retirer le statut président de tous les membres de ce jury
        JuryMember::where('jury_id', $this->jury->id)->update(['is_president' => false]);

        // Définir ce membre comme président
        JuryMember::where('id', $memberId)->update(['is_president' => true]);

        // Recharger le jury
        $this->jury = Jury::with(['members.user'])->findOrFail($this->juryId);

        $this->message = $member->user->name.' a été défini comme président du jury.';
        $this->messageType = 'success';
    }

    public function removeMember(string $memberId): void
    {
        if (! $this->canManage()) {
            $this->message = 'Vous n\'avez pas les droits pour modifier la composition de ce jury.';
            $this->messageType = 'error';

            return;
        }

        $member = JuryMember::find($memberId);
        if (! $member || $member->jury_id !== $this->jury->id) {
            $this->message = 'Membre non trouvé.';
            $this->messageType = 'error';

            return;
        }

        $memberName = $member->user->name;
        $member->delete();

        // Recharger le jury
        $this->jury = Jury::with(['members.user'])->findOrFail($this->juryId);

        $this->message = $memberName.' a été retiré du jury.';
        $this->messageType = 'success';
    }

    public function render()
    {
        // Utilisateurs éligibles (pas déjà dans ce jury)
        $currentMemberIds = $this->jury ? $this->jury->members->pluck('user_id')->toArray() : [];

        $eligibleUsers = collect();

        // Seul le super administrateur peut constituer le jury.
        if ($this->canManage()) {
            // On ne propose que les autres administrateurs (rôle "admin"), pas les super_admin.
            $eligibleUsers = User::with('roles')
                ->whereNotIn('id', $currentMemberIds)
                ->whereHas('roles', function ($query): void {
                    $query->where('name', 'admin');
                })
                ->orderBy('name')
                ->get();
        }

        $roleOptions = [
            'referent_pedagogique' => 'Référent Pédagogique',
            'directeur_pedagogique' => 'Directeur Pédagogique',
            'formateur_senior' => 'Formateur Senior',
        ];

        return view('livewire.admin.jury-add-member', [
            'canManage' => $this->canManage(),
            'eligibleUsers' => $eligibleUsers,
            'roleOptions' => $roleOptions,
        ]);
    }
}
