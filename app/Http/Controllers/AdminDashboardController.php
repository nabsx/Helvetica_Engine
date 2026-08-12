<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    /**
     * Compatibility entry point retained for existing routes and references.
     * The single dashboard implementation lives in the Livewire component.
     */
    public function index(): View
    {
        return view('admin.dashboard');
    }
}
