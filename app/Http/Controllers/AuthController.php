<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    /** Staff picker + PIN pad screen. */
    public function showLogin(): View
    {
        $staff = User::orderBy('name')->get(['id', 'name', 'role']);

        return view('pos.login', compact('staff'));
    }

    /**
     * Staff pick their name (user_id) first, then enter their PIN.
     * The PIN is verified against the hashed value — we never search
     * the table by raw PIN, since it's stored hashed.
     */
    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'pin' => ['required', 'digits_between:4,6'],
        ]);

        $user = User::findOrFail($request->user_id);

        if (! $user->is_active) {
            return back()->withErrors(['pin' => 'Akun tidak aktif. Hubungi admin.']);
        }

        if (! Hash::check($request->pin, $user->pin)) {
            return back()->withErrors(['pin' => 'PIN salah.']);
        }

        Auth::login($user);
        $request->session()->regenerate();

        // Route each role to its own module. For this MVP only the
        // cashier screen exists; admin/barista fall back to it for now.
        return $user->isAdmin()
            ? redirect()->route('admin.dashboard')
            : redirect()->route('pos.index');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('pos.login');
    }
}
