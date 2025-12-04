<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use App\Models\Candidature;
use Illuminate\Support\Facades\Storage;

class CandidatureDocumentController extends Controller
{
    public function downloadCv(Candidature $candidature)
    {
        // Vérifier que la candidature appartient à l'utilisateur connecté
        if ($candidature->user_id !== auth()->id()) {
            abort(403, 'Accès non autorisé.');
        }

        if (! $candidature->cv_path) {
            abort(404, 'CV non trouvé.');
        }

        return Storage::disk('public')->download($candidature->cv_path);
    }

    public function downloadMotivationLetter(Candidature $candidature)
    {
        // Vérifier que la candidature appartient à l'utilisateur connecté
        if ($candidature->user_id !== auth()->id()) {
            abort(403, 'Accès non autorisé.');
        }

        if (! $candidature->motivation_letter_path) {
            abort(404, 'Lettre de motivation non trouvée.');
        }

        return Storage::disk('public')->download($candidature->motivation_letter_path);
    }

    public function downloadAttachment(Candidature $candidature, int $index)
    {
        // Vérifier que la candidature appartient à l'utilisateur connecté
        if ($candidature->user_id !== auth()->id()) {
            abort(403, 'Accès non autorisé.');
        }

        $attachments = $candidature->attachments ?? [];

        if (! isset($attachments[$index]) || ! isset($attachments[$index]['path'])) {
            abort(404, 'Pièce jointe non trouvée.');
        }

        return Storage::disk('public')->download($attachments[$index]['path'], $attachments[$index]['name'] ?? 'attachment.pdf');
    }
}
