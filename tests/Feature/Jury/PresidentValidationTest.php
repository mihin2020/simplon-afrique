<?php

use App\Models\Badge;
use App\Models\Candidature;
use App\Models\CandidatureStep;
use App\Models\Evaluation;
use App\Models\EvaluationCategory;
use App\Models\EvaluationGrid;
use App\Models\Jury;
use App\Models\JuryMember;
use App\Models\LabellisationStep;
use App\Models\Role;
use App\Models\User;
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

    // Créer un président du jury
    $this->president = User::create([
        'name' => 'President',
        'email' => 'president@test.com',
        'password' => bcrypt('password'),
    ]);
    $this->president->roles()->attach($juryRole);

    // Créer un badge
    $this->badge = Badge::create([
        'name' => 'junior',
        'label' => 'Label Formateur Junior',
        'min_score' => 10.0,
        'max_score' => 12.99,
    ]);

    // Créer des étapes
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

    // Créer une grille
    $this->grid = EvaluationGrid::create([
        'name' => 'Grille Test',
        'description' => 'Grille de test',
        'is_active' => true,
    ]);

    // Créer des catégories
    $this->category1 = EvaluationCategory::create([
        'evaluation_grid_id' => $this->grid->id,
        'labellisation_step_id' => $this->step1->id,
        'name' => 'Catégorie 1',
        'display_order' => 1,
    ]);

    $this->category2 = EvaluationCategory::create([
        'evaluation_grid_id' => $this->grid->id,
        'labellisation_step_id' => $this->step2->id,
        'name' => 'Catégorie 2',
        'display_order' => 1,
    ]);

    // Créer une candidature
    $this->candidature = Candidature::create([
        'user_id' => $this->formateur->id,
        'status' => 'in_review',
        'cv_path' => 'test.pdf',
        'motivation_letter_path' => 'test.pdf',
    ]);

    // Créer des étapes complétées
    CandidatureStep::create([
        'candidature_id' => $this->candidature->id,
        'labellisation_step_id' => $this->step1->id,
        'status' => 'completed',
    ]);

    CandidatureStep::create([
        'candidature_id' => $this->candidature->id,
        'labellisation_step_id' => $this->step2->id,
        'status' => 'completed',
    ]);

    // Créer un jury
    $this->jury = Jury::create([
        'name' => 'Jury Test',
        'status' => 'constituted',
        'evaluation_grid_id' => $this->grid->id,
    ]);

    // Créer le président
    $this->presidentMember = JuryMember::create([
        'jury_id' => $this->jury->id,
        'user_id' => $this->president->id,
        'role' => 'directeur_pedagogique',
        'is_president' => true,
    ]);

    $this->candidature->juries()->attach($this->jury->id);

    // Créer des évaluations avec des moyennes
    Evaluation::create([
        'candidature_id' => $this->candidature->id,
        'jury_id' => $this->jury->id,
        'jury_member_id' => $this->presidentMember->id,
        'evaluation_grid_id' => $this->grid->id,
        'labellisation_step_id' => $this->step1->id,
        'status' => 'submitted',
        'average_score' => 11.0,
    ]);

    Evaluation::create([
        'candidature_id' => $this->candidature->id,
        'jury_id' => $this->jury->id,
        'jury_member_id' => $this->presidentMember->id,
        'evaluation_grid_id' => $this->grid->id,
        'labellisation_step_id' => $this->step2->id,
        'status' => 'submitted',
        'average_score' => 12.0,
    ]);
});

it('affiche les notes moyennes par étape', function () {
    $this->actingAs($this->president);

    Livewire::test(\App\Livewire\Jury\PresidentValidation::class, [
        'candidatureId' => $this->candidature->id,
    ])
        ->assertSee('Étape 1')
        ->assertSee('Étape 2')
        ->assertSee('11.0')
        ->assertSee('12.0');
});

it('calcule et affiche la note finale', function () {
    $this->actingAs($this->president);

    Livewire::test(\App\Livewire\Jury\PresidentValidation::class, [
        'candidatureId' => $this->candidature->id,
    ])
        ->assertSee('11.5'); // (11.0 + 12.0) / 2 = 11.5
});

it('propose le badge approprié selon la note finale', function () {
    $this->actingAs($this->president);

    Livewire::test(\App\Livewire\Jury\PresidentValidation::class, [
        'candidatureId' => $this->candidature->id,
    ])
        ->assertSee('Label Formateur Junior'); // 11.5 est dans la plage Junior
});

it('valide la candidature et attribue le badge', function () {
    $this->actingAs($this->president);

    Livewire::test(\App\Livewire\Jury\PresidentValidation::class, [
        'candidatureId' => $this->candidature->id,
    ])
        ->set('presidentComment', 'Excellent travail, candidature validée.')
        ->call('approve')
        ->assertRedirect(route('admin.candidature.show', $this->candidature->id));

    // Vérifier que la candidature a été validée
    $this->candidature->refresh();
    expect($this->candidature->status)->toBe('validated');
    expect($this->candidature->badge_id)->toBe($this->badge->id);

    // Vérifier que les évaluations ont été mises à jour
    $evaluations = Evaluation::where('candidature_id', $this->candidature->id)->get();
    foreach ($evaluations as $evaluation) {
        expect($evaluation->president_decision)->toBe('approved');
        expect($evaluation->president_comment)->toBe('Excellent travail, candidature validée.');
        expect($evaluation->president_validated_at)->not->toBeNull();
    }
});

it('rejette la candidature avec un commentaire', function () {
    $this->actingAs($this->president);

    Livewire::test(\App\Livewire\Jury\PresidentValidation::class, [
        'candidatureId' => $this->candidature->id,
    ])
        ->set('presidentComment', 'La candidature ne répond pas aux critères requis.')
        ->call('reject')
        ->assertRedirect(route('admin.candidature.show', $this->candidature->id));

    // Vérifier que la candidature a été rejetée
    $this->candidature->refresh();
    expect($this->candidature->status)->toBe('rejected');

    // Vérifier que les évaluations ont été mises à jour
    $evaluations = Evaluation::where('candidature_id', $this->candidature->id)->get();
    foreach ($evaluations as $evaluation) {
        expect($evaluation->president_decision)->toBe('rejected');
        expect($evaluation->president_comment)->toBe('La candidature ne répond pas aux critères requis.');
    }
});
