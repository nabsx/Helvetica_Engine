<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function __construct(private readonly ActivityLogService $activityLogs) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);
        $staff = User::query()->whereIn('role', ['cashier', 'barista'])
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->string('search')->trim().'%'))
            ->latest()->paginate(15)->withQueryString();

        return view('admin.users.index', compact('staff'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', User::class);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'role' => ['required', 'in:cashier,barista'],
            'pin' => ['required', 'digits_between:4,6'],
        ]);
        $user = User::create($data + ['is_active' => true]);
        $this->activityLogs->record('staff.created', $user, ['role' => $user->role], $request);

        return back()->with('success', "Staff {$user->name} berhasil ditambahkan.");
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);
        $data = $request->validate(['name' => ['required', 'string', 'max:120'], 'role' => ['required', 'in:cashier,barista']]);
        $user->update($data);
        $this->activityLogs->record('staff.updated', $user, $data, $request);

        return back()->with('success', 'Data staff diperbarui.');
    }

    public function resetPin(Request $request, User $user): RedirectResponse
    {
        $this->authorize('resetPin', $user);
        $data = $request->validate(['pin' => ['required', 'digits_between:4,6']]);
        $user->update(['pin' => $data['pin']]);
        $this->activityLogs->record('staff.pin_reset', $user, [], $request);

        return back()->with('success', "PIN {$user->name} berhasil direset.");
    }

    public function deactivate(Request $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);
        $user->update(['is_active' => false]);
        $this->activityLogs->record('staff.deactivated', $user, [], $request);

        return back()->with('success', "Staff {$user->name} dinonaktifkan.");
    }

    public function activate(Request $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);
        $user->update(['is_active' => true]);
        $this->activityLogs->record('staff.activated', $user, [], $request);

        return back()->with('success', "Staff {$user->name} diaktifkan.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->authorize('delete', $user);
        $user->delete();
        $this->activityLogs->record('staff.deleted', $user, [], $request);

        return back()->with('success', 'Staff diarsipkan tanpa menghapus histori transaksi.');
    }
}
