<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\Candidature;
use App\Services\AttestationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class AttestationController extends Controller
{
    /**
     * Télécharge l'attestation d'une candidature.
     */
    public function download(Candidature $candidature): Response
    {
        $user = Auth::user();

        // Vérifier que la candidature appartient à l'utilisateur
        if ($candidature->user_id !== $user->id) {
            abort(403, 'Vous n\'avez pas accès à cette attestation.');
        }

        // Vérifier que la candidature est validée
        if ($candidature->status !== 'validated') {
            abort(404, 'Aucune attestation disponible pour cette candidature.');
        }

        // Régénérer l'attestation pour s'assurer qu'elle contient les informations à jour (nom complet, signature, etc.)
        $attestationService = new AttestationService;
        $attestationPath = $attestationService->regenerateAttestation($candidature);

        if (! $attestationPath) {
            abort(404, 'Impossible de générer l\'attestation.');
        }

        // Vérifier que le fichier existe
        if (! Storage::disk('public')->exists($attestationPath)) {
            abort(404, 'Le fichier d\'attestation est introuvable.');
        }

        $fullPath = Storage::disk('public')->path($attestationPath);

        // Construire le nom du fichier avec le nom complet
        $firstName = trim($user->first_name ?? '');
        $lastName = trim($user->name ?? '');
        $fullName = ! empty($firstName) && ! empty($lastName)
            ? $firstName.' '.$lastName
            : ($lastName ?: $firstName ?: 'Formateur');

        $filename = 'Attestation_'.str_replace(' ', '_', $fullName).'.pdf';

        return response()->download($fullPath, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * Télécharge l'image du badge d'une candidature.
     */
    public function downloadBadge(Candidature $candidature): Response
    {
        $user = Auth::user();

        // Vérifier que la candidature appartient à l'utilisateur
        if ($candidature->user_id !== $user->id) {
            abort(403, 'Vous n\'avez pas accès à ce badge.');
        }

        // Vérifier que la candidature est validée
        if ($candidature->status !== 'validated') {
            abort(404, 'Aucun badge disponible pour cette candidature.');
        }

        // Vérifier que la candidature a un badge
        if (! $candidature->badge_id) {
            abort(404, 'Aucun badge attribué à cette candidature.');
        }

        // Charger le badge avec sa configuration
        $badge = Badge::with('configuration')->find($candidature->badge_id);

        if (! $badge || ! $badge->configuration || ! $badge->configuration->image_path) {
            abort(404, 'L\'image du badge n\'est pas disponible.');
        }

        // Vérifier que le fichier existe
        if (! Storage::disk('public')->exists($badge->configuration->image_path)) {
            abort(404, 'Le fichier du badge est introuvable.');
        }

        $fullPath = Storage::disk('public')->path($badge->configuration->image_path);

        // Construire le nom du fichier
        $firstName = trim($user->first_name ?? '');
        $lastName = trim($user->name ?? '');
        $fullName = ! empty($firstName) && ! empty($lastName)
            ? $firstName.' '.$lastName
            : ($lastName ?: $firstName ?: 'Formateur');

        // Récupérer l'extension du fichier
        $extension = pathinfo($badge->configuration->image_path, PATHINFO_EXTENSION);
        $filename = 'Badge_'.str_replace(' ', '_', $badge->name).'_'.str_replace(' ', '_', $fullName).'.'.$extension;

        // Déterminer le type MIME
        $mimeType = mime_content_type($fullPath);

        return response()->download($fullPath, $filename, [
            'Content-Type' => $mimeType,
        ]);
    }
}
