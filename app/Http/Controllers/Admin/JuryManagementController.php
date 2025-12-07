<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jury;
use Illuminate\View\View;

class JuryManagementController extends Controller
{
    public function index(): View
    {
        return view('admin.juries');
    }

    public function create(): View
    {
        $user = auth()->user()->load('roles');
        $isSuperAdmin = $user->roles->contains('name', 'super_admin');

        if (! $isSuperAdmin) {
            abort(403, 'Seul le super administrateur peut créer un jury.');
        }

        return view('admin.jury-create');
    }

    public function addMember(Jury $jury): View
    {
        return view('admin.jury-add-member', ['juryId' => $jury->id]);
    }
}
