<?php

namespace App\Livewire\Admin;

use App\Models\Badge;
use App\Models\Candidature;
use App\Models\Jury;
use App\Models\LabellisationStep;
use App\Models\Role;
use App\Models\User;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        // Total formateurs
        $formateurRole = Role::where('name', 'formateur')->first();
        $totalFormateurs = $formateurRole ? User::whereHas('roles', function ($query) use ($formateurRole) {
            $query->where('roles.id', $formateurRole->id);
        })->count() : 0;

        // Répartition des badges
        $badges = Badge::withCount(['candidatures' => function ($query) {
            $query->where('status', 'validated');
        }])->get();

        $totalBadges = $badges->sum('candidatures_count');
        $badgeDistribution = $badges->map(function ($badge) use ($totalBadges) {
            return [
                'name' => $badge->label,
                'count' => $badge->candidatures_count,
                'percentage' => $totalBadges > 0 ? round(($badge->candidatures_count / $totalBadges) * 100, 1) : 0,
            ];
        });

        // Dossiers en cours par étape
        $steps = LabellisationStep::orderBy('display_order')->get();
        $dossiersParEtape = $steps->map(function ($step) {
            $count = Candidature::where('current_step_id', $step->id)
                ->whereIn('status', ['submitted', 'in_review'])
                ->count();

            return [
                'step' => $step,
                'count' => $count,
            ];
        });

        // Total dossiers en cours
        $totalDossiersEnCours = Candidature::whereIn('status', ['submitted', 'in_review'])->count();

        // Jurys constitués
        $jurysConstitués = Jury::where('status', 'constituted')->count();
        $totalJurys = Jury::count();

        // Alertes : Nouveaux formateurs (sans profil complété ou en attente)
        $nouveauxFormateurs = User::whereHas('roles', function ($query) use ($formateurRole) {
            $query->where('roles.id', $formateurRole->id);
        })->whereDoesntHave('formateurProfile')->count();

        // Alertes : Dossiers en attente de validation finale
        $dossiersEnAttente = Candidature::where('status', 'in_review')
            ->whereNotNull('current_step_id')
            ->count();

        // Activité récente (dernières candidatures validées)
        $activiteRecente = Candidature::with(['user', 'badge'])
            ->where('status', 'validated')
            ->latest('updated_at')
            ->limit(5)
            ->get();

        return view('livewire.admin.dashboard', [
            'totalFormateurs' => $totalFormateurs,
            'badgeDistribution' => $badgeDistribution,
            'totalBadges' => $totalBadges,
            'dossiersParEtape' => $dossiersParEtape,
            'totalDossiersEnCours' => $totalDossiersEnCours,
            'jurysConstitués' => $jurysConstitués,
            'totalJurys' => $totalJurys,
            'nouveauxFormateurs' => $nouveauxFormateurs,
            'dossiersEnAttente' => $dossiersEnAttente,
            'activiteRecente' => $activiteRecente,
        ]);
    }
}
