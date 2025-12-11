<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class ActivationController extends Controller
{
    public function showCreatePasswordForm(Request $request, User $user): View
    {
        // Vérifier la signature de l'URL
        if (! $this->validateSignature($request, $user)) {
            abort(403, 'Lien d\'activation invalide ou expiré.');
        }

        // Extraire la date d'expiration de la requête pour réutiliser la même expiration
        $expires = $request->query('expires');

        // Générer une nouvelle URL signée pour le formulaire POST avec la même expiration
        // Si expires n'est pas présent, utiliser une expiration par défaut de 7 jours
        $expiration = $expires ? now()->setTimestamp((int) $expires) : now()->addDays(7);
        
        $signedUrl = URL::temporarySignedRoute(
            'activation.store-password',
            $expiration,
            ['user' => $user->id]
        );

        return view('auth.create-password', [
            'user' => $user,
            'signedUrl' => $signedUrl,
        ]);
    }

    public function createPassword(Request $request, User $user): RedirectResponse
    {
        // Vérifier la signature de l'URL
        if (! $this->validateSignature($request, $user)) {
            abort(403, 'Lien d\'activation invalide ou expiré.');
        }

        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('login')
            ->with('status', 'Votre mot de passe a été créé avec succès. Vous pouvez maintenant vous connecter.');
    }

    /**
     * Valide la signature de l'URL signée temporairement
     */
    private function validateSignature(Request $request, User $user): bool
    {
        // Utiliser la vérification native de Laravel qui gère automatiquement les signatures temporaires
        // hasValidSignature() vérifie à la fois la signature et l'expiration
        return $request->hasValidSignature();
    }
}
