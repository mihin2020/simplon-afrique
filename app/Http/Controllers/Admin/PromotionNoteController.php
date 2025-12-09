<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePromotionNoteRequest;
use App\Models\PromotionNote;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PromotionNoteController extends Controller
{
    /**
     * Display the follow-up page with list of administrators.
     */
    public function index(): View
    {
        return view('admin.follow-up.index');
    }

    /**
     * Display notes for a specific administrator.
     */
    public function show(User $admin): View
    {
        return view('admin.follow-up.index', ['selectedAdmin' => $admin]);
    }

    /**
     * Store a newly created note.
     */
    public function store(StorePromotionNoteRequest $request, User $admin): RedirectResponse
    {
        $validated = $request->validated();
        $validated['admin_id'] = $admin->id;
        $validated['created_by'] = Auth::id();

        PromotionNote::create($validated);

        return redirect()->route('admin.follow-up.show', $admin)
            ->with('message', 'Note ajoutée avec succès.');
    }

    /**
     * Display notes for the current administrator (for admin users).
     */
    public function myNotes(): View
    {
        return view('admin.my-notes');
    }
}
