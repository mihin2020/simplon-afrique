<?php

namespace App\Events;

use App\Models\Candidature;
use App\Models\Evaluation;
use App\Models\LabellisationStep;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EvaluationSubmitted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Evaluation $evaluation,
        public Candidature $candidature,
        public LabellisationStep $step
    ) {}
}
