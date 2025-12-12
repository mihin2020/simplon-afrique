<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class FormateurController extends Controller
{
    public function dashboard(): View
    {
        return view('formateur.dashboard');
    }

    public function profile(): View
    {
        return view('formateur.profile');
    }

    public function createCandidature(): View
    {
        return view('formateur.create-candidature');
    }

    public function myCandidatures(): View
    {
        return view('formateur.my-candidatures');
    }
}








