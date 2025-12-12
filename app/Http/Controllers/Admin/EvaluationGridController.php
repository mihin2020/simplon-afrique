<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EvaluationGrid;
use Illuminate\View\View;

class EvaluationGridController extends Controller
{
    public function index(): View
    {
        return view('admin.evaluation-grids');
    }

    public function create(): View
    {
        return view('admin.evaluation-grid-create');
    }

    public function show(EvaluationGrid $grid): View
    {
        return view('admin.evaluation-grid', ['gridId' => $grid->id]);
    }
}








