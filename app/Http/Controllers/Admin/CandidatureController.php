<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Candidature;
use App\Models\CandidatureStep;
use App\Models\Jury;
use App\Models\LabellisationStep;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CandidatureController extends Controller
{
    /**
     * Affiche le détail d'une candidature.
     */
    public function show(Candidature $candidature): View
    {
        $candidature->load([
            'user.formateurProfile',
            'badge',
            'currentStep',
            'steps.labellisationStep',
            'juries.members.user',
        ]);

        $user = Auth::user()->load('roles');
        $isSuperAdmin = $user->roles->contains('name', 'super_admin');

        // Récupérer tous les jurys constitués ou en cours (avec au moins un membre)
        $availableJuries = Jury::with('members.user')
            ->whereIn('status', ['constituted', 'in_progress'])
            ->has('members')
            ->orderBy('name')
            ->get();

        $roleOptions = [
            'referent_pedagogique' => 'Référent Pédagogique',
            'directeur_pedagogique' => 'Directeur Pédagogique',
            'formateur_senior' => 'Formateur Senior',
        ];

        $canAssignJury = $isSuperAdmin &&
                        $candidature->status === 'in_review' &&
                        $candidature->current_step_id !== null;

        return view('admin.candidature-detail', [
            'candidature' => $candidature,
            'isSuperAdmin' => $isSuperAdmin,
            'availableJuries' => $availableJuries,
            'roleOptions' => $roleOptions,
            'canAssignJury' => $canAssignJury,
        ]);
    }

    /**
     * Valide une candidature (passe de submitted à in_review avec l'étape 1).
     */
    public function validate(Candidature $candidature): RedirectResponse
    {
        $user = Auth::user()->load('roles');
        $isSuperAdmin = $user->roles->contains('name', 'super_admin');

        // Vérifier les permissions
        if (! $isSuperAdmin) {
            return redirect()
                ->route('admin.candidature.show', $candidature)
                ->with('error', 'Seul le super administrateur peut valider une candidature.');
        }

        // Vérifier que la candidature est soumise
        if ($candidature->status !== 'submitted') {
            return redirect()
                ->route('admin.candidature.show', $candidature)
                ->with('error', 'Cette candidature n\'est pas en attente de validation. Statut actuel : '.$candidature->status);
        }

        try {
            // Récupérer la première étape (step 1)
            $firstStep = LabellisationStep::orderBy('display_order')->first();

            if (! $firstStep) {
                return redirect()
                    ->route('admin.candidature.show', $candidature)
                    ->with('error', 'Aucune étape de labellisation trouvée.');
            }

            // Créer ou mettre à jour le CandidatureStep pour la première étape
            CandidatureStep::updateOrCreate(
                [
                    'candidature_id' => $candidature->id,
                    'labellisation_step_id' => $firstStep->id,
                ],
                [
                    'status' => 'in_progress',
                ]
            );

            // Mettre à jour la candidature
            $candidature->update([
                'status' => 'in_review',
                'current_step_id' => $firstStep->id,
            ]);

            return redirect()
                ->route('admin.candidature.show', $candidature)
                ->with('success', 'La candidature a été validée avec succès. L\'étape 1 ('.$firstStep->label.') est maintenant en cours. Vous pouvez maintenant assigner un jury.');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.candidature.show', $candidature)
                ->with('error', 'Une erreur est survenue : '.$e->getMessage());
        }
    }

    /**
     * Assigner un jury à une candidature.
     */
    public function assignJury(Candidature $candidature): RedirectResponse
    {
        $user = Auth::user()->load('roles');
        $isSuperAdmin = $user->roles->contains('name', 'super_admin');

        // Vérifier les permissions
        if (! $isSuperAdmin) {
            return redirect()
                ->route('admin.candidature.show', $candidature)
                ->with('error', 'Seul le super administrateur peut assigner ou retirer un jury.');
        }

        $validated = request()->validate([
            'jury_id' => ['nullable', 'string', 'uuid', 'exists:juries,id'],
        ]);

        $juryId = $validated['jury_id'] ?? null;

        try {
            if ($juryId) {
                // Pour l'assignation, vérifier que la candidature est validée (step 1 validée)
                if ($candidature->status !== 'in_review' || ! $candidature->current_step_id) {
                    return redirect()
                        ->route('admin.candidature.show', $candidature)
                        ->with('error', 'Vous devez d\'abord valider la candidature (étape 1) avant d\'assigner un jury.');
                }
                // Vérifier que le jury existe et est constitué
                $jury = Jury::with('members.user')->find($juryId);

                if (! $jury) {
                    return redirect()
                        ->route('admin.candidature.show', $candidature)
                        ->with('error', 'Le jury sélectionné n\'existe pas.');
                }

                if ($jury->members->isEmpty()) {
                    return redirect()
                        ->route('admin.candidature.show', $candidature)
                        ->with('error', 'Ce jury n\'est pas encore constitué. Veuillez d\'abord ajouter des membres au jury.');
                }

                // Vérifier si la candidature a déjà un jury assigné
                $existingJury = $candidature->juries->first();
                if ($existingJury && $existingJury->id !== $jury->id) {
                    // Retirer l'ancien jury
                    $candidature->juries()->detach($existingJury->id);

                    // Mettre à jour le statut de l'ancien jury s'il n'a plus de candidatures
                    $otherCandidatures = $existingJury->candidatures()->where('candidatures.id', '!=', $candidature->id)->count();
                    if ($otherCandidatures === 0 && $existingJury->status === 'in_progress') {
                        $existingJury->update(['status' => 'constituted']);
                    }
                }

                // Assigner le nouveau jury à la candidature
                $candidature->juries()->sync([$juryId]);

                // Mettre à jour le statut du jury
                if ($jury->status === 'constituted') {
                    $jury->update(['status' => 'in_progress']);
                }

                $membersCount = $jury->members->count();

                return redirect()
                    ->route('admin.candidature.show', $candidature)
                    ->with('success', 'Le jury "'.$jury->name.'" ('.$membersCount.' membre'.($membersCount > 1 ? 's' : '').') a été assigné à cette candidature. Les membres du jury peuvent maintenant procéder à l\'évaluation.');
            } else {
                // Retirer l'assignation
                $assignedJury = $candidature->juries->first();

                if ($assignedJury) {
                    $juryName = $assignedJury->name;
                    $candidature->juries()->detach();

                    // Mettre à jour le statut du jury s'il n'a plus de candidatures
                    $otherCandidatures = $assignedJury->candidatures()->count();
                    if ($otherCandidatures === 0 && $assignedJury->status === 'in_progress') {
                        $assignedJury->update(['status' => 'constituted']);
                    }

                    return redirect()
                        ->route('admin.candidature.show', $candidature)
                        ->with('success', 'Le jury "'.$juryName.'" a été retiré de cette candidature.');
                }

                return redirect()
                    ->route('admin.candidature.show', $candidature)
                    ->with('info', 'Aucun jury n\'était assigné à cette candidature.');
            }
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.candidature.show', $candidature)
                ->with('error', 'Une erreur est survenue : '.$e->getMessage());
        }
    }
}
