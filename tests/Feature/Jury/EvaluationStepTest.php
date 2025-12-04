<?php

use App\Events\EvaluationSubmitted;
use App\Models\Candidature;
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
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;

beforeEach(function () {
    // Créer les rôles
    $formateurRole = Role::firstOrCreate(['name' => 'formateur']);
    $juryRole = Role::firstOrCreate(['name' => 'jury']);

    // Créer un formateur
    $this->formateur = User::create([
        'name' => 'Formateur Test',
        'email' => 'formateur@test.com',
        'password' => bcrypt('password'),
    ]);
    $this->formateur->roles()->attach($formateurRole);

    // Créer un membre du jury
    $this->juryMember = User::create([
        'name' => 'Jury Member',
        'email' => 'jury@test.com',
        'password' => bcrypt('password'),
    ]);
    $this->juryMember->roles()->attach($juryRole);

    // Créer une étape
    $this->step = LabellisationStep::create([
        'name' => 'step1',
        'label' => 'Étape 1',
        'display_order' => 1,
    ]);

    // Créer une grille
    $this->grid = EvaluationGrid::create([
        'name' => 'Grille Test',
        'description' => 'Grille de test',
        'is_active' => true,
    ]);

    // Créer une catégorie
    $this->category = EvaluationCategory::create([
        'evaluation_grid_id' => $this->grid->id,
        'labellisation_step_id' => $this->step->id,
        'name' => 'Catégorie 1',
        'display_order' => 1,
    ]);

    // Créer des critères
    $this->criterion1 = EvaluationCriterion::create([
        'evaluation_category_id' => $this->category->id,
        'name' => 'Critère 1',
        'weight' => 50.0,
        'display_order' => 1,
    ]);

    $this->criterion2 = EvaluationCriterion::create([
        'evaluation_category_id' => $this->category->id,
        'name' => 'Critère 2',
        'weight' => 50.0,
        'display_order' => 2,
    ]);

    // Créer une candidature
    $this->candidature = Candidature::create([
        'user_id' => $this->formateur->id,
        'status' => 'in_review',
        'current_step_id' => $this->step->id,
        'cv_path' => 'test.pdf',
        'motivation_letter_path' => 'test.pdf',
    ]);

    // Créer un jury
    $this->jury = Jury::create([
        'name' => 'Jury Test',
        'status' => 'constituted',
        'evaluation_grid_id' => $this->grid->id,
    ]);

    // Créer le membre du jury
    $this->juryMemberModel = JuryMember::create([
        'jury_id' => $this->jury->id,
        'user_id' => $this->juryMember->id,
        'role' => 'referent_pedagogique',
        'is_president' => false,
    ]);

    $this->candidature->juries()->attach($this->jury->id);
});

it('affiche les critères de l\'étape courante', function () {
    $this->actingAs($this->juryMember);

    Livewire::test(\App\Livewire\Jury\EvaluateStep::class, [
        'candidatureId' => $this->candidature->id,
        'stepId' => $this->step->id,
    ])
        ->assertSee('Critère 1')
        ->assertSee('Critère 2')
        ->assertSee('50.0%'); // Poids
});

it('calcule automatiquement la note pondérée en temps réel', function () {
    $this->actingAs($this->juryMember);

    Livewire::test(\App\Livewire\Jury\EvaluateStep::class, [
        'candidatureId' => $this->candidature->id,
        'stepId' => $this->step->id,
    ])
        ->set("scores.{$this->criterion1->id}", 15.0)
        ->assertSee('7.5'); // 15 * 0.50 = 7.5
});

it('soumet l\'évaluation avec tous les critères notés', function () {
    Event::fake();

    $this->actingAs($this->juryMember);

    Livewire::test(\App\Livewire\Jury\EvaluateStep::class, [
        'candidatureId' => $this->candidature->id,
        'stepId' => $this->step->id,
    ])
        ->set("scores.{$this->criterion1->id}", 15.0)
        ->set("scores.{$this->criterion2->id}", 18.0)
        ->call('submit')
        ->assertHasNoErrors();

    // Vérifier que l'évaluation a été créée
    $evaluation = Evaluation::where('candidature_id', $this->candidature->id)
        ->where('labellisation_step_id', $this->step->id)
        ->where('jury_member_id', $this->juryMemberModel->id)
        ->first();

    expect($evaluation)->not->toBeNull();
    expect($evaluation->status)->toBe('submitted');

    // Vérifier que les scores ont été créés
    $scores = EvaluationScore::where('evaluation_id', $evaluation->id)->get();
    expect($scores)->toHaveCount(2);

    // Vérifier que l'événement a été déclenché
    Event::assertDispatched(EvaluationSubmitted::class);
});

it('ne permet pas de soumettre si tous les critères ne sont pas notés', function () {
    $this->actingAs($this->juryMember);

    Livewire::test(\App\Livewire\Jury\EvaluateStep::class, [
        'candidatureId' => $this->candidature->id,
        'stepId' => $this->step->id,
    ])
        ->set("scores.{$this->criterion1->id}", 15.0)
        // Ne pas noter le critère 2
        ->call('submit');

    // Vérifier que l'évaluation n'a pas été créée
    $evaluation = Evaluation::where('candidature_id', $this->candidature->id)
        ->where('labellisation_step_id', $this->step->id)
        ->where('jury_member_id', $this->juryMemberModel->id)
        ->first();

    expect($evaluation)->toBeNull();
});
