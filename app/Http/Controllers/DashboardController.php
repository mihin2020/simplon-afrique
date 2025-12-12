<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class DashboardController extends Controller
{
    public function redirect(): RedirectResponse
    {
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

        return redirect('/');
    }
}








