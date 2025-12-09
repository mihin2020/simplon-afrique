<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class BadgeAttestationSettingsController extends Controller
{
    public function index(): View
    {
        return view('admin.badge-attestation-settings');
    }
}


