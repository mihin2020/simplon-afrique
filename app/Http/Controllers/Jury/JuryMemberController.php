<?php

namespace App\Http\Controllers\Jury;

use App\Http\Controllers\Controller;
use App\Models\Candidature;
use App\Models\LabellisationStep;
use Illuminate\View\View;

class JuryMemberController extends Controller
{
    public function dashboard(): View
    {
        return view('jury.dashboard');
    }

    public function evaluateStep(Candidature $candidature, LabellisationStep $step): View
    {
        return view('jury.evaluate-step', [
            'candidatureId' => $candidature->id,
            'stepId' => $step->id,
        ]);
    }

    public function presidentValidation(Candidature $candidature): View
    {
        return view('jury.president-validation', [
            'candidatureId' => $candidature->id,
        ]);
    }

    public function viewEvaluations(Candidature $candidature): View
    {
        return view('jury.view-evaluations', [
            'candidatureId' => $candidature->id,
        ]);
    }
}


