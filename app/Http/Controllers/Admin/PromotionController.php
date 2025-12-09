<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePromotionRequest;
use App\Http\Requests\UpdatePromotionRequest;
use App\Models\Promotion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PromotionController extends Controller
{
    /**
     * Display a listing of promotions.
     */
    public function index(): View
    {
        return view('admin.promotions.index');
    }

    /**
     * Show the form for creating a new promotion.
     */
    public function create(): View
    {
        return view('admin.promotions.index');
    }

    /**
     * Store a newly created promotion in storage.
     */
    public function store(StorePromotionRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $organizationIds = $validated['organization_ids'] ?? [];
        unset($validated['organization_ids']);
        $validated['created_by'] = Auth::id();

        $promotion = Promotion::create($validated);
        $promotion->organizations()->sync($organizationIds);

        return redirect()->route('admin.promotions')
            ->with('message', 'Promotion créée avec succès.');
    }

    /**
     * Show the form for editing the specified promotion.
     */
    public function edit(Promotion $promotion): View
    {
        return view('admin.promotions.index', ['editingPromotion' => $promotion]);
    }

    /**
     * Update the specified promotion in storage.
     */
    public function update(UpdatePromotionRequest $request, Promotion $promotion): RedirectResponse
    {
        $validated = $request->validated();
        $organizationIds = $validated['organization_ids'] ?? [];
        unset($validated['organization_ids']);
        $promotion->update($validated);
        $promotion->organizations()->sync($organizationIds);

        return redirect()->route('admin.promotions')
            ->with('message', 'Promotion mise à jour avec succès.');
    }

    /**
     * Remove the specified promotion from storage.
     */
    public function destroy(Promotion $promotion): RedirectResponse
    {
        $promotion->delete();

        return redirect()->route('admin.promotions')
            ->with('message', 'Promotion supprimée avec succès.');
    }
}
