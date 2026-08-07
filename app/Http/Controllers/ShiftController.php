<?php

namespace App\Http\Controllers;

use App\Http\Requests\CloseShiftRequest;
use App\Http\Requests\OpenShiftRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ShiftController extends Controller
{
    /**
     * Open a new cashier shift with an opening cash float.
     * A user may not open a second shift while one is already open —
     * this is the gate that everything else (EnsureShiftIsOpen) relies on.
     */
    public function open(OpenShiftRequest $request): JsonResponse
    {
        $user = Auth::user();

        if ($user->shifts()->open()->exists()) {
            return response()->json([
                'message' => 'Anda masih memiliki shift yang sedang berjalan.',
            ], 422);
        }

        $shift = $user->shifts()->create([
            'start_time' => now(),
            'initial_cash' => $request->validated('initial_cash'),
            'status' => 'open',
        ]);

        return response()->json([
            'message' => 'Shift berhasil dibuka.',
            'shift' => $shift,
        ], 201);
    }

    /**
     * Close the user's currently open shift, reconciling the physically
     * counted cash against the system-expected cash (see Shift model's
     * expected_cash / variance accessors).
     */
    public function close(CloseShiftRequest $request): JsonResponse
    {
        $user = Auth::user();

        $shift = $user->shifts()->open()->latest('start_time')->first();

        if (! $shift) {
            return response()->json([
                'message' => 'Tidak ada shift aktif untuk ditutup.',
            ], 422);
        }

        $shift->update([
            'end_time' => now(),
            'actual_cash' => $request->validated('actual_cash'),
            'status' => 'closed',
        ]);

        return response()->json([
            'message' => 'Shift berhasil ditutup.',
            'shift' => $shift->fresh(), // includes expected_cash & variance via $appends
        ]);
    }
}
