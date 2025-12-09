<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\JobOffer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class JobOfferController extends Controller
{
    /**
     * Display a listing of the job offers (Super Admin).
     */
    public function index(): View
    {
        return view('admin.job-offers');
    }

    /**
     * Show the form for creating a new job offer.
     */
    public function create(): View
    {
        return view('admin.job-offer-form');
    }

    /**
     * Display the specified job offer.
     */
    public function show(JobOffer $jobOffer): View
    {
        return view('admin.job-offer-detail', [
            'jobOfferId' => $jobOffer->id,
        ]);
    }

    /**
     * Show the form for editing the specified job offer.
     */
    public function edit(JobOffer $jobOffer): View
    {
        return view('admin.job-offer-form', [
            'jobOfferId' => $jobOffer->id,
        ]);
    }

    /**
     * Download the job offer attachment.
     */
    public function downloadAttachment(JobOffer $jobOffer): StreamedResponse|RedirectResponse
    {
        if (! $jobOffer->attachment_path || ! Storage::disk('public')->exists($jobOffer->attachment_path)) {
            return redirect()->back()->with('error', 'Le fichier n\'existe pas.');
        }

        return Storage::disk('public')->download(
            $jobOffer->attachment_path,
            'offre_'.$jobOffer->id.'_'.basename($jobOffer->attachment_path)
        );
    }

    /**
     * Display the specified job application.
     */
    public function showApplication(JobApplication $application): View
    {
        return view('admin.job-application-detail', [
            'applicationId' => $application->id,
        ]);
    }

    /**
     * Download the application CV.
     */
    public function downloadApplicationCv(JobApplication $application): StreamedResponse|RedirectResponse
    {
        if (! $application->cv_path || ! Storage::disk('public')->exists($application->cv_path)) {
            return redirect()->back()->with('error', 'Le CV n\'existe pas.');
        }

        $applicantName = ($application->profile_snapshot['first_name'] ?? '').'_'.($application->profile_snapshot['name'] ?? '');
        $applicantName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $applicantName);

        return Storage::disk('public')->download(
            $application->cv_path,
            'CV_'.$applicantName.'.pdf'
        );
    }

    /**
     * Display the public list of job offers (for admins and formateurs, not super_admin).
     */
    public function publicIndex(): View|RedirectResponse
    {
        // Le super_admin ne doit pas accéder aux offres publiques
        if (auth()->user()?->hasRole('super_admin')) {
            return redirect()->route('admin.job-offers')
                ->with('error', 'Les super administrateurs doivent gérer les offres via la section administration.');
        }

        return view('job-offers.index');
    }

    /**
     * Display the public job offer detail (for admins and formateurs, not super_admin).
     */
    public function publicShow(JobOffer $jobOffer): View|RedirectResponse
    {
        // Le super_admin ne doit pas accéder aux offres publiques
        if (auth()->user()?->hasRole('super_admin')) {
            return redirect()->route('admin.job-offers')
                ->with('error', 'Les super administrateurs doivent gérer les offres via la section administration.');
        }

        // S'assurer que l'offre est publiée
        if (! $jobOffer->isPublished()) {
            return redirect()->route('job-offers.index')
                ->with('error', 'Cette offre n\'est pas disponible.');
        }

        return view('job-offers.detail', [
            'jobOffer' => $jobOffer,
        ]);
    }
}
