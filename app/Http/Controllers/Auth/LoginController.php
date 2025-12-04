<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, remember: false)) {
            $request->session()->regenerate();

            $user = Auth::user()->load('roles');
            $redirectTo = $this->getRedirectPath($user);

            return redirect()->intended($redirectTo);
        }

        return back()
            ->withErrors([
                'email' => 'Les identifiants fournis sont incorrects.',
            ])
            ->onlyInput('email');
    }

    /**
     * Détermine la route de redirection selon le rôle de l'utilisateur.
     */
    private function getRedirectPath($user): string
    {
        $roles = $user->roles->pluck('name')->toArray();

        if (in_array('super_admin', $roles)) {
            return '/admin/dashboard';
        }

        if (in_array('admin', $roles)) {
            return '/admin/dashboard';
        }

        if (in_array('formateur', $roles)) {
            return '/formateur/dashboard';
        }

        if (in_array('jury', $roles)) {
            return '/jury/dashboard';
        }

        // Par défaut, redirection vers la page d'accueil
        return '/';
    }
}
