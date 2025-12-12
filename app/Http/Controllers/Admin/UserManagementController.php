<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function dashboard(): View
    {
        return view('admin.dashboard');
    }

    public function index(): View
    {
        $user = auth()->user()->load('roles');
        $userRoles = $user->roles->pluck('name')->toArray();

        if (! in_array('super_admin', $userRoles) && ! in_array('admin', $userRoles)) {
            abort(403, 'Accès non autorisé. Vous devez être super admin ou admin.');
        }

        return view('admin.user-management');
    }
}





