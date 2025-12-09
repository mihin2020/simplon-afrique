<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\Candidature;
use App\Models\Jury;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class CertificationController extends Controller
{
    /**
     * Affiche la liste des certifications (formateurs certifiés).
     * Affiche uniquement les formateurs que l'utilisateur a évalués ou dont il fait partie du jury.
     */
    public function index()
    {
        if (! Auth::check()) {
            abort(403);
        }

        /** @var User $user */
        $user = Auth::user();
        $user->load('roles');
        $isSuperAdmin = $user->roles->contains('name', 'super_admin');
        $isReferent = $user->isReferentPedagogique() && ! empty($user->country);

        // Récupérer les IDs des jurys dont l'utilisateur est membre
        $userJuryIds = Jury::whereHas('members', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->pluck('id')->toArray();

        // Base query pour les candidatures liées aux jurys de l'utilisateur
        $baseQuery = function ($query) use ($userJuryIds, $isSuperAdmin, $isReferent, $user) {
            if ($isReferent) {
                // Pour les référents pédagogiques : filtrer par pays et organisations
                $query->forReferent($user);
            } elseif (! $isSuperAdmin && ! empty($userJuryIds)) {
                // Pour les membres du jury : uniquement les candidatures de leurs jurys
                $query->whereHas('juries', function ($q) use ($userJuryIds) {
                    $q->whereIn('juries.id', $userJuryIds);
                });
            } elseif (! $isSuperAdmin) {
                // Si l'utilisateur n'est dans aucun jury et n'est pas super admin, ne rien afficher
                $query->whereRaw('1 = 0');
            }
            // Super admin voit tout
        };

        // Récupérer les candidatures validées avec leurs badges
        $certifiedCandidatures = Candidature::with(['user', 'badge', 'juries'])
            ->where('status', 'validated')
            ->whereNotNull('badge_id')
            ->where(function ($query) use ($baseQuery) {
                $baseQuery($query);
            })
            ->orderBy('updated_at', 'desc')
            ->get();

        // Récupérer les candidatures rejetées
        $rejectedCandidatures = Candidature::with(['user', 'badge', 'juries'])
            ->where('status', 'rejected')
            ->where(function ($query) use ($baseQuery) {
                $baseQuery($query);
            })
            ->orderBy('updated_at', 'desc')
            ->get();

        // Statistiques par badge (basées sur les candidatures filtrées)
        $badges = Badge::all();
        $badgeStats = $badges->mapWithKeys(function ($badge) use ($certifiedCandidatures) {
            return [$badge->id => [
                'name' => $badge->label ?? $badge->name,
                'count' => $certifiedCandidatures->where('badge_id', $badge->id)->count(),
            ]];
        });

        // Statistiques globales
        $stats = [
            'total_certified' => $certifiedCandidatures->count(),
            'total_rejected' => $rejectedCandidatures->count(),
            'by_badge' => $badgeStats,
        ];

        return view('admin.certifications', [
            'certifiedCandidatures' => $certifiedCandidatures,
            'rejectedCandidatures' => $rejectedCandidatures,
            'badges' => $badges,
            'stats' => $stats,
            'isSuperAdmin' => $isSuperAdmin,
            'userJuryIds' => $userJuryIds,
        ]);
    }
}