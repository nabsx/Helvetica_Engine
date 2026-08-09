<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PosController extends Controller
{
    public function index(): View
    {
        $categories = Category::with(['products' => fn ($q) => $q->available()])
            ->whereHas('products', fn ($q) => $q->available())
            ->get();

        $activeShift = Auth::user()->shifts()->open()->latest('start_time')->first();

        return view('pos.index', compact('categories', 'activeShift'));
    }
}
