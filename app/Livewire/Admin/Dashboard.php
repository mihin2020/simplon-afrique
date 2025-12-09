<?php

namespace App\Livewire\Admin;

use App\Models\Badge;
use App\Models\Candidature;
use App\Models\Jury;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        /** @var User|null $currentUser */
        $currentUser = Auth::user();

        // Total formateurs
        $formateurRole = Role::where('name', 'formateur')->first();
        $formateursQuery = User::whereHas('roles', function ($query) use ($formateurRole) {
            $query->where('roles.id', $formateurRole->id);
        });

        // Appliquer le filtre référent pédagogique si applicable
        if ($currentUser) {
            $formateursQuery->forReferent($currentUser);
        }
        $totalFormateurs = $formateursQuery->count();

        // Répartition des badges
        $badgesQuery = Badge::query();
        if ($currentUser) {
            $badgesQuery->withCount(['candidatures' => function ($query) use ($currentUser) {
                $query->where('status', 'validated')
                    ->forReferent($currentUser);
            }]);
        } else {
            $badgesQuery->withCount(['candidatures' => function ($query) {
                $query->where('status', 'validated');
            }]);
        }
        $badges = $badgesQuery->get();

        $totalBadges = $badges->sum('candidatures_count');
        $badgeDistribution = $badges->map(function ($badge) use ($totalBadges) {
            return [
                'name' => $badge->label,
                'count' => $badge->candidatures_count,
                'percentage' => $totalBadges > 0 ? round(($badge->candidatures_count / $totalBadges) * 100, 1) : 0,
            ];
        });

        // Total dossiers en cours
        $dossiersQuery = Candidature::whereIn('status', ['submitted', 'in_review']);
        if ($currentUser) {
            $dossiersQuery->forReferent($currentUser);
        }
        $totalDossiersEnCours = $dossiersQuery->count();

        // Jurys constitués (filtrer par candidatures du référent)
        $jurysQuery = Jury::where('status', 'constituted');
        if ($currentUser && $currentUser->isReferentPedagogique() && ! empty($currentUser->country)) {
            $jurysQuery->whereHas('candidatures', function ($q) use ($currentUser) {
                $q->forReferent($currentUser);
            });
        }
        $jurysConstitués = $jurysQuery->count();

        $totalJurysQuery = Jury::query();
        if ($currentUser && $currentUser->isReferentPedagogique() && ! empty($currentUser->country)) {
            $totalJurysQuery->whereHas('candidatures', function ($q) use ($currentUser) {
                $q->forReferent($currentUser);
            });
        }
        $totalJurys = $totalJurysQuery->count();

        // Alertes : Nouveaux formateurs (sans profil complété ou en attente)
        $nouveauxFormateursQuery = User::whereHas('roles', function ($query) use ($formateurRole) {
            $query->where('roles.id', $formateurRole->id);
        })->whereDoesntHave('formateurProfile');

        if ($currentUser) {
            $nouveauxFormateursQuery->forReferent($currentUser);
        }
        $nouveauxFormateurs = $nouveauxFormateursQuery->count();

        // Alertes : Dossiers en attente de validation finale
        $dossiersEnAttenteQuery = Candidature::where('status', 'in_review')
            ->whereNotNull('current_step_id');

        if ($currentUser) {
            $dossiersEnAttenteQuery->forReferent($currentUser);
        }
        $dossiersEnAttente = $dossiersEnAttenteQuery->count();

        // Activité récente (dernières candidatures validées)
        $activiteRecenteQuery = Candidature::with(['user', 'badge'])
            ->where('status', 'validated')
            ->latest('updated_at')
            ->limit(5);

        if ($currentUser) {
            $activiteRecenteQuery->forReferent($currentUser);
        }
        $activiteRecente = $activiteRecenteQuery->get();

        return view('livewire.admin.dashboard', [
            'totalFormateurs' => $totalFormateurs,
            'badgeDistribution' => $badgeDistribution,
            'totalBadges' => $totalBadges,
            'totalDossiersEnCours' => $totalDossiersEnCours,
            'jurysConstitués' => $jurysConstitués,
            'totalJurys' => $totalJurys,
            'nouveauxFormateurs' => $nouveauxFormateurs,
            'dossiersEnAttente' => $dossiersEnAttente,
            'activiteRecente' => $activiteRecente,
        ]);
    }
}