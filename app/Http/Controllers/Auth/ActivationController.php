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
        // Vérifier la signature en acceptant localhost ou 127.0.0.1
        $isValid = $this->validateSignature($request, $user);

        if (! $isValid) {
            abort(403, 'Lien d\'activation invalide ou expiré.');
        }

        // Extraire les paramètres de signature de la requête GET pour les utiliser dans le formulaire POST
        $signature = $request->query('signature');
        $expires = $request->query('expires');

        // Construire l'URL POST signée avec les mêmes paramètres
        $signedUrl = route('activation.store-password', ['user' => $user->id]);
        if ($signature && $expires) {
            $signedUrl .= '?signature='.urlencode($signature).'&expires='.urlencode($expires);
        }

        return view('auth.create-password', [
            'user' => $user,
            'signedUrl' => $signedUrl,
        ]);
    }

    public function createPassword(Request $request, User $user): RedirectResponse
    {
        // Vérifier la signature en acceptant localhost ou 127.0.0.1
        $isValid = $this->validateSignature($request, $user);

        if (! $isValid) {
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
     * Valide la signature de l'URL en acceptant localhost ou 127.0.0.1
     */
    private function validateSignature(Request $request, User $user): bool
    {
        // Essayer d'abord avec la vérification standard
        if ($request->hasValidSignature()) {
            return true;
        }

        // Si ça échoue, essayer avec l'autre domaine (localhost <-> 127.0.0.1)
        $currentUrl = $request->getSchemeAndHttpHost();
        $originalUrl = config('app.url');

        // Si les URLs diffèrent (localhost vs 127.0.0.1), essayer de régénérer avec le bon domaine
        if ($currentUrl !== $originalUrl) {
            $originalUrlParsed = parse_url($originalUrl);
            $currentUrlParsed = parse_url($currentUrl);

            // Si seul le hostname diffère (localhost vs 127.0.0.1), accepter les deux
            if (($originalUrlParsed['host'] ?? '') === 'localhost' &&
                ($currentUrlParsed['host'] ?? '') === '127.0.0.1' ||
                ($originalUrlParsed['host'] ?? '') === '127.0.0.1' &&
                ($currentUrlParsed['host'] ?? '') === 'localhost') {

                // Vérifier manuellement la signature en utilisant le path et les paramètres
                $expires = $request->query('expires');
                $signature = $request->query('signature');

                if (! $expires || ! $signature) {
                    return false;
                }

                // Vérifier l'expiration
                if ((int) $expires < now()->timestamp) {
                    return false;
                }

                // Reconstruire l'URL pour la vérification (sans le domaine)
                $path = '/activation/'.$user->id;

                // Générer la signature attendue
                $key = config('app.key');
                $expectedSignature = hash_hmac('sha256', $path.$expires, $key);

                return hash_equals($expectedSignature, $signature);
            }
        }

        return false;
    }
}
