<?php

use App\Models\Badge;
use App\Models\Candidature;
use App\Models\CandidatureStep;
use App\Models\Evaluation;
use App\Models\EvaluationCategory;
use App\Models\EvaluationCriterion;
use App\Models\EvaluationGrid;
use App\Models\EvaluationScore;
use App\Models\Jury;
use App\Models\JuryMember;
use App\Models\LabellisationStep;
use App\Models\Role;
use App\Models\User;
use App\Services\EvaluationCalculationService;

beforeEach(function () {
    // Créer un rôle formateur
    $formateurRole = Role::firstOrCreate(['name' => 'formateur']);

    // Créer un utilisateur formateur
    $this->formateur = User::create([
        'name' => 'Formateur Test',
        'email' => 'formateur@test.com',
        'password' => bcrypt('password'),
    ]);
    $this->formateur->roles()->attach($formateurRole);

    // Créer des badges
    $this->juniorBadge = Badge::create([
        'name' => 'junior',
        'label' => 'Label Formateur Junior',
        'min_score' => 10.0,
        'max_score' => 12.99,
    ]);

    $this->intermediaireBadge = Badge::create([
        'name' => 'intermediaire',
        'label' => 'Label Formateur Intermédiaire',
        'min_score' => 13.0,
        'max_score' => 15.99,
    ]);

    $this->seniorBadge = Badge::create([
        'name' => 'senior',
        'label' => 'Label Formateur Senior',
        'min_score' => 16.0,
        'max_score' => 20.0,
    ]);

    // Créer des étapes de labellisation
    $this->step1 = LabellisationStep::create([
        'name' => 'step1',
        'label' => 'Étape 1',
        'display_order' => 1,
    ]);

    $this->step2 = LabellisationStep::create([
        'name' => 'step2',
        'label' => 'Étape 2',
        'display_order' => 2,
    ]);

    // Créer une grille d'évaluation
    $this->grid = EvaluationGrid::create([
        'name' => 'Grille Test',
        'description' => 'Grille de test',
        'is_active' => true,
    ]);

    // Créer des catégories liées aux étapes
    $this->category1 = EvaluationCategory::create([
        'evaluation_grid_id' => $this->grid->id,
        'labellisation_step_id' => $this->step1->id,
        'name' => 'Catégorie 1',
        'description' => 'Description catégorie 1',
        'display_order' => 1,
    ]);

    $this->category2 = EvaluationCategory::create([
        'evaluation_grid_id' => $this->grid->id,
        'labellisation_step_id' => $this->step2->id,
        'name' => 'Catégorie 2',
        'description' => 'Description catégorie 2',
        'display_order' => 1,
    ]);

    // Créer des critères avec poids
    $this->criterion1 = EvaluationCriterion::create([
        'evaluation_category_id' => $this->category1->id,
        'name' => 'Critère 1',
        'description' => 'Description critère 1',
        'weight' => 30.0, // 30%
        'display_order' => 1,
    ]);

    $this->criterion2 = EvaluationCriterion::create([
        'evaluation_category_id' => $this->category1->id,
        'name' => 'Critère 2',
        'description' => 'Description critère 2',
        'weight' => 70.0, // 70%
        'display_order' => 2,
    ]);

    $this->service = new EvaluationCalculationService;
});

it('calcule correctement la note pondérée', function () {
    $rawScore = 15.0;
    $weight = 30.0; // 30%

    $weightedScore = $this->service->calculateWeightedScore($rawScore, $weight);

    expect($weightedScore)->toBe(4.5); // 15 * 0.30 = 4.5
});

it('calcule correctement la somme des notes pondérées pour un membre', function () {
    // Créer une candidature
    $candidature = Candidature::create([
        'user_id' => $this->formateur->id,
        'status' => 'in_review',
        'current_step_id' => $this->step1->id,
        'cv_path' => 'test.pdf',
        'motivation_letter_path' => 'test.pdf',
    ]);

    // Créer un jury
    $jury = Jury::create([
        'name' => 'Jury Test',
        'status' => 'constituted',
        'evaluation_grid_id' => $this->grid->id,
    ]);

    $memberUser = User::create([
        'name' => 'Membre Test',
        'email' => 'membre@test.com',
        'password' => bcrypt('password'),
    ]);

    $juryMember = JuryMember::create([
        'jury_id' => $jury->id,
        'user_id' => $memberUser->id,
        'role' => 'referent_pedagogique',
        'is_president' => false,
    ]);

    $candidature->juries()->attach($jury->id);

    // Créer une évaluation
    $evaluation = Evaluation::create([
        'candidature_id' => $candidature->id,
        'jury_id' => $jury->id,
        'jury_member_id' => $juryMember->id,
        'evaluation_grid_id' => $this->grid->id,
        'labellisation_step_id' => $this->step1->id,
        'status' => 'submitted',
    ]);

    // Créer des scores
    EvaluationScore::create([
        'evaluation_id' => $evaluation->id,
        'evaluation_criterion_id' => $this->criterion1->id,
        'raw_score' => 15.0,
        'weighted_score' => 4.5, // 15 * 0.30
    ]);

    EvaluationScore::create([
        'evaluation_id' => $evaluation->id,
        'evaluation_criterion_id' => $this->criterion2->id,
        'raw_score' => 18.0,
        'weighted_score' => 12.6, // 18 * 0.70
    ]);

    $total = $this->service->calculateMemberTotalScore($evaluation);

    expect($total)->toBe(17.1); // 4.5 + 12.6 = 17.1
});

it('calcule correctement la moyenne d\'une étape', function () {
    // Créer une candidature
    $candidature = Candidature::create([
        'user_id' => $this->formateur->id,
        'status' => 'in_review',
        'current_step_id' => $this->step1->id,
        'cv_path' => 'test.pdf',
        'motivation_letter_path' => 'test.pdf',
    ]);

    // Créer un jury avec 2 membres
    $jury = Jury::create([
        'name' => 'Jury Test',
        'status' => 'constituted',
        'evaluation_grid_id' => $this->grid->id,
    ]);

    $member1User = User::create([
        'name' => 'Membre 1',
        'email' => 'membre1@test.com',
        'password' => bcrypt('password'),
    ]);

    $member2User = User::create([
        'name' => 'Membre 2',
        'email' => 'membre2@test.com',
        'password' => bcrypt('password'),
    ]);

    $member1 = JuryMember::create([
        'jury_id' => $jury->id,
        'user_id' => $member1User->id,
        'role' => 'referent_pedagogique',
        'is_president' => false,
    ]);

    $member2 = JuryMember::create([
        'jury_id' => $jury->id,
        'user_id' => $member2User->id,
        'role' => 'directeur_pedagogique',
        'is_president' => false,
    ]);

    $candidature->juries()->attach($jury->id);

    // Créer les évaluations
    $evaluation1 = Evaluation::create([
        'candidature_id' => $candidature->id,
        'jury_id' => $jury->id,
        'jury_member_id' => $member1->id,
        'evaluation_grid_id' => $this->grid->id,
        'labellisation_step_id' => $this->step1->id,
        'status' => 'submitted',
    ]);

    $evaluation2 = Evaluation::create([
        'candidature_id' => $candidature->id,
        'jury_id' => $jury->id,
        'jury_member_id' => $member2->id,
        'evaluation_grid_id' => $this->grid->id,
        'labellisation_step_id' => $this->step1->id,
        'status' => 'submitted',
    ]);

    // Créer les scores pour le membre 1 (total = 17.1)
    EvaluationScore::create([
        'evaluation_id' => $evaluation1->id,
        'evaluation_criterion_id' => $this->criterion1->id,
        'raw_score' => 15.0,
        'weighted_score' => 4.5,
    ]);

    EvaluationScore::create([
        'evaluation_id' => $evaluation1->id,
        'evaluation_criterion_id' => $this->criterion2->id,
        'raw_score' => 18.0,
        'weighted_score' => 12.6,
    ]);

    // Créer les scores pour le membre 2 (total = 16.0)
    EvaluationScore::create([
        'evaluation_id' => $evaluation2->id,
        'evaluation_criterion_id' => $this->criterion1->id,
        'raw_score' => 14.0,
        'weighted_score' => 4.2,
    ]);

    EvaluationScore::create([
        'evaluation_id' => $evaluation2->id,
        'evaluation_criterion_id' => $this->criterion2->id,
        'raw_score' => 16.86,
        'weighted_score' => 11.8,
    ]);

    $average = $this->service->calculateStepAverage($candidature, $this->step1);

    // Moyenne = (17.1 + 16.0) / 2 = 16.55
    expect(round($average, 2))->toBe(16.55);
    expect(round($evaluation1->fresh()->average_score, 2))->toBe(16.55);
    expect(round($evaluation2->fresh()->average_score, 2))->toBe(16.55);
});

it('détermine correctement le badge selon les seuils', function () {
    // Créer une candidature
    $candidature = Candidature::create([
        'user_id' => $this->formateur->id,
        'status' => 'in_review',
        'cv_path' => 'test.pdf',
        'motivation_letter_path' => 'test.pdf',
    ]);

    // Créer des étapes complétées avec des moyennes
    CandidatureStep::create([
        'candidature_id' => $candidature->id,
        'labellisation_step_id' => $this->step1->id,
        'status' => 'completed',
    ]);

    CandidatureStep::create([
        'candidature_id' => $candidature->id,
        'labellisation_step_id' => $this->step2->id,
        'status' => 'completed',
    ]);

    // Créer un jury
    $jury = Jury::create([
        'name' => 'Jury Test',
        'status' => 'constituted',
        'evaluation_grid_id' => $this->grid->id,
    ]);

    $memberUser = User::create([
        'name' => 'Membre Test',
        'email' => 'membre@test.com',
        'password' => bcrypt('password'),
    ]);

    $member = JuryMember::create([
        'jury_id' => $jury->id,
        'user_id' => $memberUser->id,
        'role' => 'referent_pedagogique',
        'is_president' => false,
    ]);

    $candidature->juries()->attach($jury->id);

    // Créer des évaluations avec des moyennes
    Evaluation::create([
        'candidature_id' => $candidature->id,
        'jury_id' => $jury->id,
        'jury_member_id' => $member->id,
        'evaluation_grid_id' => $this->grid->id,
        'labellisation_step_id' => $this->step1->id,
        'status' => 'submitted',
        'average_score' => 11.0, // Junior
    ]);

    Evaluation::create([
        'candidature_id' => $candidature->id,
        'jury_id' => $jury->id,
        'jury_member_id' => $member->id,
        'evaluation_grid_id' => $this->grid->id,
        'labellisation_step_id' => $this->step2->id,
        'status' => 'submitted',
        'average_score' => 14.0, // Intermédiaire
    ]);

    // Note finale = (11.0 + 14.0) / 2 = 12.5 (Junior)
    $badge = $this->service->determineBadge($candidature);

    expect($badge)->not->toBeNull();
    expect($badge->name)->toBe('junior');
});

it('retourne null si la note finale est inférieure à 10', function () {
    $candidature = Candidature::create([
        'user_id' => $this->formateur->id,
        'status' => 'in_review',
        'cv_path' => 'test.pdf',
        'motivation_letter_path' => 'test.pdf',
    ]);

    CandidatureStep::create([
        'candidature_id' => $candidature->id,
        'labellisation_step_id' => $this->step1->id,
        'status' => 'completed',
    ]);

    $jury = Jury::create([
        'name' => 'Jury Test',
        'status' => 'constituted',
        'evaluation_grid_id' => $this->grid->id,
    ]);

    $memberUser = User::create([
        'name' => 'Membre Test',
        'email' => 'membre@test.com',
        'password' => bcrypt('password'),
    ]);

    $member = JuryMember::create([
        'jury_id' => $jury->id,
        'user_id' => $memberUser->id,
        'role' => 'referent_pedagogique',
        'is_president' => false,
    ]);

    $candidature->juries()->attach($jury->id);

    Evaluation::create([
        'candidature_id' => $candidature->id,
        'jury_id' => $jury->id,
        'jury_member_id' => $member->id,
        'evaluation_grid_id' => $this->grid->id,
        'labellisation_step_id' => $this->step1->id,
        'status' => 'submitted',
        'average_score' => 8.0, // < 10
    ]);

    $badge = $this->service->determineBadge($candidature);

    expect($badge)->toBeNull();
});
