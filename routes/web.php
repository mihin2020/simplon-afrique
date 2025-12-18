<?php

use App\Http\Controllers\Admin\BadgeAttestationSettingsController;
use App\Http\Controllers\Admin\CandidatureController;
use App\Http\Controllers\Admin\CandidatureDocumentController;
use App\Http\Controllers\Admin\CandidatureManagementController;
use App\Http\Controllers\Admin\CertificationController;
use App\Http\Controllers\Admin\EvaluationGridController;
use App\Http\Controllers\Admin\JobOfferController;
use App\Http\Controllers\Admin\JuryController;
use App\Http\Controllers\Admin\JuryManagementController;
use App\Http\Controllers\Admin\LabellisationSettingsController;
use App\Http\Controllers\Admin\OrganizationController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\PromotionNoteController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Auth\ActivationController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Formateur\AttestationController;
use App\Http\Controllers\Formateur\CandidatureDocumentController as FormateurCandidatureDocumentController;
use App\Http\Controllers\Formateur\FormateurController;
use App\Http\Controllers\Admin\TrainerNotificationController;
use App\Http\Controllers\Jury\JuryMemberController;
use App\Http\Controllers\LogoutController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    // Réinitialisation de mot de passe
    Route::get('/password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])
        ->name('password.request');
    Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])
        ->name('password.email');
    Route::get('/password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])
        ->name('password.reset');
    Route::post('/password/reset', [ResetPasswordController::class, 'reset'])
        ->name('password.update');

    // Activation du compte
    Route::get('/activation/{user}', [ActivationController::class, 'showCreatePasswordForm'])
        ->name('activation.create-password');
    Route::post('/activation/{user}', [ActivationController::class, 'createPassword'])
        ->name('activation.store-password');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'redirect'])->name('dashboard');
    Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');

    // Routes Formateur
    Route::middleware('role:formateur')->prefix('formateur')->name('formateur.')->group(function () {
        Route::get('/dashboard', [FormateurController::class, 'dashboard'])->name('dashboard');
        Route::get('/profile', [FormateurController::class, 'profile'])->name('profile');
        Route::get('/candidature/create', [FormateurController::class, 'createCandidature'])->name('create-candidature');
        Route::get('/candidatures', [FormateurController::class, 'myCandidatures'])->name('candidatures');

        Route::prefix('candidature/{candidature}')->name('candidature.')->group(function () {
            Route::get('/download-cv', [FormateurCandidatureDocumentController::class, 'downloadCv'])
                ->name('download-cv');
            Route::get('/download-motivation', [FormateurCandidatureDocumentController::class, 'downloadMotivationLetter'])
                ->name('download-motivation');
            Route::get('/download-attachment/{index}', [FormateurCandidatureDocumentController::class, 'downloadAttachment'])
                ->name('download-attachment');
        });

        Route::get('/attestation/{candidature}/download', [AttestationController::class, 'download'])
            ->name('attestation.download');

        Route::get('/badge/{candidature}/download', [AttestationController::class, 'downloadBadge'])
            ->name('badge.download');
    });

    // Routes Admin / Super Admin
    Route::middleware('role:super_admin,admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [UserManagementController::class, 'dashboard'])->name('dashboard');
        Route::get('/profile', function () {
            return view('admin.profile');
        })->name('profile');
        Route::get('/users', [UserManagementController::class, 'index'])->name('users');
        Route::get('/candidatures', [CandidatureManagementController::class, 'index'])->name('candidatures');
        Route::get('/certifications', [CertificationController::class, 'index'])->name('certifications');
        Route::get('/juries', [JuryManagementController::class, 'index'])->name('juries');

        // Route pour créer un jury (doit être avant jury/{jury} pour éviter les conflits)
        Route::middleware('role:super_admin')->get('/jury/create', [JuryManagementController::class, 'create'])->name('jury.create');

        Route::prefix('candidature/{candidature}')->name('candidature.')->group(function () {
            Route::get('/', [CandidatureController::class, 'show'])->name('show');
            Route::post('/validate', [CandidatureController::class, 'validate'])->name('validate');
            Route::post('/assign-jury', [CandidatureController::class, 'assignJury'])->name('assign-jury');
            Route::get('/download-cv', [CandidatureDocumentController::class, 'downloadCv'])->name('download-cv');
            Route::get('/download-motivation', [CandidatureDocumentController::class, 'downloadMotivationLetter'])
                ->name('download-motivation');
            Route::get('/download-attachment/{index}', [CandidatureDocumentController::class, 'downloadAttachment'])
                ->name('download-attachment');
        });

        Route::prefix('jury/{jury}')->name('jury.')->group(function () {
            Route::get('/', [JuryController::class, 'showDetail'])->name('detail');
            Route::get('/add-member', [JuryManagementController::class, 'addMember'])->name('add-member');
            Route::get('/evaluation/{candidature}', [JuryController::class, 'showEvaluationForm'])->name('evaluation');
            Route::post('/evaluation/{candidature}', [JuryController::class, 'saveEvaluation'])->name('evaluation.save');
            Route::post('/president-validate/{candidature}', [JuryController::class, 'presidentValidate'])->name('president-validate');
        });

        // Routes Mes Notes - Administrateurs uniquement
        Route::get('/my-notes', [PromotionNoteController::class, 'myNotes'])->name('my-notes');
    });

    // Routes Super Admin uniquement
    Route::middleware('role:super_admin')->prefix('admin')->name('admin.')->group(function () {
        Route::post('/jury/{jury}/remove-member/{member}', [JuryController::class, 'removeMember'])
            ->name('jury.remove-member');
        Route::post('/jury/{jury}/update-evaluation-grid', [JuryController::class, 'updateEvaluationGrid'])
            ->name('jury.update-evaluation-grid');

        Route::get('/evaluation-grids', [EvaluationGridController::class, 'index'])->name('evaluation-grids');
        Route::prefix('evaluation-grids')->name('evaluation-grids.')->group(function () {
            Route::get('/create', [EvaluationGridController::class, 'create'])->name('create');
            Route::get('/{grid}', [EvaluationGridController::class, 'show'])->name('detail');
        });

        Route::get('/organizations', [OrganizationController::class, 'index'])->name('organizations');
        Route::get('/labellisation-settings', [LabellisationSettingsController::class, 'index'])->name('labellisation-settings');
        Route::get('/badge-attestation-settings', [BadgeAttestationSettingsController::class, 'index'])->name('badge-attestation-settings');

        // Routes Promotions - Super Admin uniquement
        Route::get('/promotions', [PromotionController::class, 'index'])->name('promotions');
        Route::get('/promotions/create', [PromotionController::class, 'create'])->name('promotions.create');
        Route::post('/promotions', [PromotionController::class, 'store'])->name('promotions.store');
        Route::get('/promotions/{promotion}/edit', [PromotionController::class, 'edit'])->name('promotions.edit');
        Route::put('/promotions/{promotion}', [PromotionController::class, 'update'])->name('promotions.update');
        Route::delete('/promotions/{promotion}', [PromotionController::class, 'destroy'])->name('promotions.destroy');

        // Routes Suivi - Super Admin uniquement
        Route::get('/follow-up', [PromotionNoteController::class, 'index'])->name('follow-up');
        Route::get('/follow-up/{admin}/notes', [PromotionNoteController::class, 'show'])->name('follow-up.show');
        Route::post('/follow-up/{admin}/notes', [PromotionNoteController::class, 'store'])->name('follow-up.store');

        // Routes Offres d'emploi - Super Admin
        Route::get('/job-offers', [JobOfferController::class, 'index'])->name('job-offers');
        Route::get('/job-offers/create', [JobOfferController::class, 'create'])->name('job-offers.create');
        Route::get('/job-offers/{jobOffer}', [JobOfferController::class, 'show'])->name('job-offers.show');
        Route::get('/job-offers/{jobOffer}/edit', [JobOfferController::class, 'edit'])->name('job-offers.edit');
        Route::get('/job-offers/{jobOffer}/attachment', [JobOfferController::class, 'downloadAttachment'])->name('job-offers.attachment');
        Route::get('/job-application/{application}', [JobOfferController::class, 'showApplication'])->name('job-application.show');
        Route::get('/job-application/{application}/cv', [JobOfferController::class, 'downloadApplicationCv'])->name('job-application.cv');

        // Routes Notifications formateurs - Super Admin uniquement
        Route::get('/trainer-notifications', [TrainerNotificationController::class, 'index'])->name('trainer-notifications.index');
        Route::post('/trainer-notifications', [TrainerNotificationController::class, 'store'])->name('trainer-notifications.store');
        Route::patch('/trainer-notifications/{notification}', [TrainerNotificationController::class, 'toggle'])->name('trainer-notifications.toggle');
    });

    // Routes Jury
    Route::middleware('role:jury')->prefix('jury')->name('jury.')->group(function () {
        Route::get('/dashboard', [JuryMemberController::class, 'dashboard'])->name('dashboard');
        Route::get('/evaluate/{candidature}/{step}', [JuryMemberController::class, 'evaluateStep'])->name('evaluate-step');
        Route::get('/candidature/{candidature}/validate', [JuryMemberController::class, 'presidentValidation'])->name('president-validation');
        Route::get('/candidature/{candidature}/view', [JuryMemberController::class, 'viewEvaluations'])->name('view-evaluations');
    });

    // Routes Offres d'emploi - Accessibles uniquement aux admins et formateurs (pas au super_admin)
    Route::middleware('role:admin,formateur')->group(function () {
        Route::get('/job-offers', [JobOfferController::class, 'publicIndex'])->name('job-offers.index');
        Route::get('/job-offers/{jobOffer}', [JobOfferController::class, 'publicShow'])->name('job-offers.detail');
    });
});
