<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTrainerNotificationRequest;
use App\Models\TrainerNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TrainerNotificationController extends Controller
{
    /**
     * Affiche la page de gestion des notifications formateurs.
     */
    public function index(): View
    {
        $notifications = TrainerNotification::query()
            ->orderByDesc('created_at')
            ->get();

        return view('admin.trainer-notifications', [
            'notifications' => $notifications,
        ]);
    }

    /**
     * Enregistre une nouvelle notification formateur.
     */
    public function store(StoreTrainerNotificationRequest $request): RedirectResponse
    {
        $data = $request->validated();

        TrainerNotification::query()->create([
            'title' => $data['title'],
            'description' => $data['description'],
            'deadline_at' => $data['deadline_at'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('admin.trainer-notifications.index')
            ->with('success', 'La notification a été créée avec succès.');
    }

    /**
     * Active ou désactive une notification existante.
     */
    public function toggle(TrainerNotification $notification): RedirectResponse
    {
        $notification->update([
            'is_active' => ! $notification->is_active,
        ]);

        return redirect()
            ->route('admin.trainer-notifications.index')
            ->with('success', 'La notification a été mise à jour.');
    }
}
