<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user()->load('roles');
        $userRoles = $user->roles->pluck('name')->toArray();

        // Super admin a accès à tout
        if (in_array('super_admin', $userRoles)) {
            return $next($request);
        }

        // Vérifier si l'utilisateur a au moins un des rôles requis
        foreach ($roles as $role) {
            if (in_array($role, $userRoles)) {
                return $next($request);
            }
        }

        // Pas de permission - message plus détaillé pour le debug
        abort(403, 'Accès non autorisé. Rôles requis: '.implode(', ', $roles).'. Vos rôles: '.implode(', ', $userRoles));
    }
}
