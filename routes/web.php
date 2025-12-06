<?php

use App\Http\Controllers\Auth\ActivationController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])
        ->name('login');

    Route::post('/login', [LoginController::class, 'login']);

    // Réinitialisation de mot de passe
    Route::get('/password/reset', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])
        ->name('password.request');

    Route::post('/password/email', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])
        ->name('password.email');

    Route::get('/password/reset/{token}', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'showResetForm'])
        ->name('password.reset');

    Route::post('/password/reset', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'reset'])
        ->name('password.update');

    // Activation du compte (création du mot de passe)
    Route::get('/activation/{user}', [ActivationController::class, 'showCreatePasswordForm'])
        ->name('activation.create-password');

    Route::post('/activation/{user}', [ActivationController::class, 'createPassword'])
        ->name('activation.store-password');
});

Route::middleware('auth')->group(function () {
    // Route générique /dashboard qui redirige vers le bon dashboard selon le rôle
    Route::get('/dashboard', function () {
        $user = auth()->user()->load('roles');
        $roles = $user->roles->pluck('name')->toArray();

        if (in_array('super_admin', $roles) || in_array('admin', $roles)) {
            return redirect()->route('admin.dashboard');
        }

        if (in_array('formateur', $roles)) {
            return redirect()->route('formateur.dashboard');
        }

        if (in_array('jury', $roles)) {
            return redirect()->route('jury.dashboard');
        }

        // Par défaut, redirection vers la page d'accueil
        return redirect('/');
    })->name('dashboard');

    Route::post('/logout', function () {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect('/login');
    })->name('logout');

    // Route de test pour vérifier les rôles (à supprimer en production)
    Route::get('/test-roles', function () {
        $user = auth()->user()->load('roles');

        return response()->json([
            'user' => $user->name,
            'email' => $user->email,
            'roles' => $user->roles->pluck('name')->toArray(),
            'is_super_admin' => $user->roles->contains('name', 'super_admin'),
        ]);
    })->name('test.roles');

    // Dashboard Formateur
    Route::middleware('role:formateur')->group(function () {
        Route::get('/formateur/dashboard', function () {
            return view('formateur.dashboard');
        })->name('formateur.dashboard');

        Route::get('/formateur/profile', function () {
            return view('formateur.profile');
        })->name('formateur.profile');

        Route::get('/formateur/candidature/create', function () {
            return view('formateur.create-candidature');
        })->name('formateur.create-candidature');

        Route::get('/formateur/candidatures', function () {
            return view('formateur.my-candidatures');
        })->name('formateur.candidatures');

        Route::get('/formateur/candidature/{candidature}/download-cv', [\App\Http\Controllers\Formateur\CandidatureDocumentController::class, 'downloadCv'])
            ->name('formateur.candidature.download-cv');

        Route::get('/formateur/candidature/{candidature}/download-motivation', [\App\Http\Controllers\Formateur\CandidatureDocumentController::class, 'downloadMotivationLetter'])
            ->name('formateur.candidature.download-motivation');

        Route::get('/formateur/candidature/{candidature}/download-attachment/{index}', [\App\Http\Controllers\Formateur\CandidatureDocumentController::class, 'downloadAttachment'])
            ->name('formateur.candidature.download-attachment');
    });

    // Dashboard Admin / Super Admin
    Route::middleware('role:super_admin,admin')->group(function () {
        Route::get('/admin/dashboard', function () {
            return view('admin.dashboard');
        })->name('admin.dashboard');
    });

    // Gestion des utilisateurs
    // Super Admin : peut gérer formateurs ET administrateurs
    // Admin : peut gérer uniquement les formateurs
    Route::middleware('role:super_admin,admin')->group(function () {
        Route::get('/admin/users', function () {
            // Vérification supplémentaire pour debug
            $user = auth()->user()->load('roles');
            $userRoles = $user->roles->pluck('name')->toArray();

            if (! in_array('super_admin', $userRoles) && ! in_array('admin', $userRoles)) {
                abort(403, 'Accès non autorisé. Vous devez être super admin ou admin.');
            }

            return view('admin.user-management');
        })->name('admin.users');

        // Gestion des candidatures (dossiers)
        Route::get('/admin/candidatures', function () {
            return view('admin.candidatures');
        })->name('admin.candidatures');

        // Gestion des certifications
        Route::get('/admin/certifications', [\App\Http\Controllers\Admin\CertificationController::class, 'index'])
            ->name('admin.certifications');

        Route::get('/admin/candidature/{candidature}', [\App\Http\Controllers\Admin\CandidatureController::class, 'show'])
            ->name('admin.candidature.show');

        Route::post('/admin/candidature/{candidature}/validate', [\App\Http\Controllers\Admin\CandidatureController::class, 'validate'])
            ->name('admin.candidature.validate');

        Route::post('/admin/candidature/{candidature}/assign-jury', [\App\Http\Controllers\Admin\CandidatureController::class, 'assignJury'])
            ->name('admin.candidature.assign-jury');

        Route::get('/admin/candidature/{candidature}/download-cv', [\App\Http\Controllers\Admin\CandidatureDocumentController::class, 'downloadCv'])
            ->name('admin.candidature.download-cv');

        Route::get('/admin/candidature/{candidature}/download-motivation', [\App\Http\Controllers\Admin\CandidatureDocumentController::class, 'downloadMotivationLetter'])
            ->name('admin.candidature.download-motivation');

        Route::get('/admin/candidature/{candidature}/download-attachment/{index}', [\App\Http\Controllers\Admin\CandidatureDocumentController::class, 'downloadAttachment'])
            ->name('admin.candidature.download-attachment');

        // Gestion des jurys
        Route::get('/admin/juries', function () {
            return view('admin.juries');
        })->name('admin.juries');

        Route::get('/admin/jury/create', function () {
            return view('admin.jury-create');
        })->name('admin.jury.create');

        Route::get('/admin/jury/{jury}', [\App\Http\Controllers\Admin\JuryController::class, 'showDetail'])
            ->name('admin.jury.detail');

        Route::get('/admin/jury/{jury}/add-member', function (\App\Models\Jury $jury) {
            return view('admin.jury-add-member', ['juryId' => $jury->id]);
        })->name('admin.jury.add-member');

        // Évaluation d'une candidature
        Route::get('/admin/jury/{jury}/evaluation/{candidature}', [\App\Http\Controllers\Admin\JuryController::class, 'showEvaluationForm'])
            ->name('admin.jury.evaluation');

        Route::post('/admin/jury/{jury}/evaluation/{candidature}', [\App\Http\Controllers\Admin\JuryController::class, 'saveEvaluation'])
            ->name('admin.jury.evaluation.save');

        // Validation par le président du jury
        Route::post('/admin/jury/{jury}/president-validate/{candidature}', [\App\Http\Controllers\Admin\JuryController::class, 'presidentValidate'])
            ->name('admin.jury.president-validate');

        // Actions du jury (uniquement super admin)
        Route::middleware('role:super_admin')->group(function () {
            Route::post('/admin/jury/{jury}/remove-member/{member}', [\App\Http\Controllers\Admin\JuryController::class, 'removeMember'])
                ->name('admin.jury.remove-member');

            Route::post('/admin/jury/{jury}/update-evaluation-grid', [\App\Http\Controllers\Admin\JuryController::class, 'updateEvaluationGrid'])
                ->name('admin.jury.update-evaluation-grid');
        });
    });

    // Gestion des grilles d'évaluation (uniquement super admin)
    Route::middleware('role:super_admin')->group(function () {
        Route::get('/admin/evaluation-grids', function () {
            return view('admin.evaluation-grids');
        })->name('admin.evaluation-grids');

        Route::get('/admin/evaluation-grids/create', function () {
            return view('admin.evaluation-grid-create');
        })->name('admin.evaluation-grids.create');

        Route::get('/admin/evaluation-grid/{grid}', function (\App\Models\EvaluationGrid $grid) {
            return view('admin.evaluation-grid', ['gridId' => $grid->id]);
        })->name('admin.evaluation-grid.detail');

        // Gestion des organisations (uniquement super admin)
        Route::get('/admin/organizations', function () {
            return view('admin.organizations');
        })->name('admin.organizations');
    });

    // Routes pour les membres du jury
    Route::middleware('role:jury')->group(function () {
        Route::get('/jury/dashboard', function () {
            return view('jury.dashboard');
        })->name('jury.dashboard');

        Route::get('/jury/evaluate/{candidature}/{step}', function (\App\Models\Candidature $candidature, \App\Models\LabellisationStep $step) {
            return view('jury.evaluate-step', [
                'candidatureId' => $candidature->id,
                'stepId' => $step->id,
            ]);
        })->name('jury.evaluate-step');

        Route::get('/jury/candidature/{candidature}/validate', function (\App\Models\Candidature $candidature) {
            return view('jury.president-validation', [
                'candidatureId' => $candidature->id,
            ]);
        })->name('jury.president-validation');

        Route::get('/jury/candidature/{candidature}/view', function (\App\Models\Candidature $candidature) {
            return view('jury.view-evaluations', [
                'candidatureId' => $candidature->id,
            ]);
        })->name('jury.view-evaluations');
    });
});
