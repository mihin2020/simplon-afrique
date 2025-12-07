<?php

use App\Livewire\Formateur\Profile;
use App\Models\FormateurProfile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

it('préremplit les informations personnelles depuis l’utilisateur', function () {
    $user = User::factory()->create([
        'name' => 'Nom Test',
        'first_name' => 'Prénom Test',
        'email' => 'email@test.com',
    ]);

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->assertSet('name', 'Nom Test')
        ->assertSet('firstName', 'Prénom Test')
        ->assertSet('email', 'email@test.com');
});

it('affiche le nom du CV existant', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    $profile = FormateurProfile::create([
        'user_id' => $user->id,
        'cv_path' => 'formateurs/cv/existant.pdf',
    ]);

    Storage::disk('public')->put($profile->cv_path, 'dummy');

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->assertSet('cvPreview', 'existant.pdf');
});

it('enregistre un nouveau CV et met à jour la prévisualisation', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    $component = Livewire::actingAs($user)
        ->test(Profile::class);

    $cv = UploadedFile::fake()->create('cv.pdf', 200, 'application/pdf');

    $component->set('cv', $cv)
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('cvPreview', 'cv.pdf')
        ->assertSet('cv', null);

    $profile = FormateurProfile::where('user_id', $user->id)->first();

    expect($profile)->not->toBeNull();
    expect($profile->cv_path)->not->toBeNull();
    Storage::disk('public')->assertExists($profile->cv_path);
});

it('met à jour les informations personnelles de l’utilisateur', function () {
    $user = User::factory()->create([
        'name' => 'Ancien Nom',
        'first_name' => 'Ancien Prénom',
        'email' => 'ancien@test.com',
    ]);

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->set('name', 'Nouveau Nom')
        ->set('firstName', 'Nouveau Prénom')
        ->set('email', 'nouveau@test.com')
        ->call('save')
        ->assertHasNoErrors();

    $user->refresh();

    expect($user->name)->toBe('Nouveau Nom');
    expect($user->first_name)->toBe('Nouveau Prénom');
    expect($user->email)->toBe('nouveau@test.com');
});

it('permet de supprimer le CV existant', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    $profile = FormateurProfile::create([
        'user_id' => $user->id,
        'cv_path' => 'formateurs/cv/existant.pdf',
    ]);

    Storage::disk('public')->put($profile->cv_path, 'dummy');

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->call('removeCv')
        ->assertSet('cvPreview', null);

    expect($profile->fresh()->cv_path)->toBeNull();
    Storage::disk('public')->assertMissing('formateurs/cv/existant.pdf');
});
