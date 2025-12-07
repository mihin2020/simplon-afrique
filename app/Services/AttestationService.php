<?php

namespace App\Services;

use App\Models\AttestationSetting;
use App\Models\Badge;
use App\Models\Candidature;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class AttestationService
{
    /**
     * Génère et sauvegarde une attestation PDF pour une candidature.
     */
    public function generateAttestation(Candidature $candidature, Badge $badge, float $score): string
    {
        // Charger le user avec first_name
        $formateur = $candidature->user->fresh(['formateurProfile']);
        $settings = AttestationSetting::getSettings();
        $date = Carbon::now();

        // Générer le PDF
        $pdf = Pdf::loadView('pdf.attestation', [
            'candidature' => $candidature,
            'formateur' => $formateur,
            'badge' => $badge->load('configuration'),
            'score' => $score,
            'settings' => $settings,
            'date' => $date,
        ]);

        // Configuration du PDF
        $pdf->setPaper('A4', 'landscape');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
        ]);

        // Nom du fichier
        $filename = sprintf(
            'attestations/attestation_%s_%s.pdf',
            str_replace(' ', '_', $formateur->name),
            $date->format('Y-m-d_His')
        );

        // Sauvegarder le PDF
        Storage::disk('public')->put($filename, $pdf->output());

        return $filename;
    }

    /**
     * Télécharge une attestation existante.
     */
    public function downloadAttestation(Candidature $candidature)
    {
        if (! $candidature->attestation_path) {
            return null;
        }

        $fullPath = Storage::disk('public')->path($candidature->attestation_path);

        if (! file_exists($fullPath)) {
            return null;
        }

        $formateur = $candidature->user;
        $filename = sprintf('Attestation_%s.pdf', str_replace(' ', '_', $formateur->name));

        return response()->download($fullPath, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * Régénère une attestation pour une candidature.
     */
    public function regenerateAttestation(Candidature $candidature): ?string
    {
        if (! $candidature->badge_id || ! $candidature->badge_awarded_at) {
            return null;
        }

        $badge = Badge::find($candidature->badge_id);
        if (! $badge) {
            return null;
        }

        // Calculer le score final
        $calculationService = new EvaluationCalculationService;
        $score = $calculationService->calculateFinalScore($candidature);

        // Supprimer l'ancienne attestation si elle existe
        if ($candidature->attestation_path) {
            Storage::disk('public')->delete($candidature->attestation_path);
        }

        // Générer la nouvelle attestation
        $path = $this->generateAttestation($candidature, $badge, $score);

        // Mettre à jour la candidature
        $candidature->update(['attestation_path' => $path]);

        return $path;
    }
}
