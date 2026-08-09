<?php

namespace App\Http\Controllers;

use App\Http\Requests\CloseShiftRequest;
use App\Http\Requests\OpenShiftRequest;
use Illuminate\Http\JsonResponse;
use App\Services\CashDrawerService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

        $shift = DB::transaction(function () use ($user, $request) {
            // A pending_close shift has already been closed by the cashier and
            // is only waiting for admin review. It must not block the next shift.
            if ($user->shifts()->where('status', 'open')->lockForUpdate()->exists()) {
                abort(422, 'Anda masih memiliki shift yang sedang berjalan.');
            }

            $openingCash = (float) $request->validated('initial_cash');
            return $user->shifts()->create([
                'start_time' => now(),
                'initial_cash' => $openingCash,
                'opening_cash' => $openingCash,
                'opened_by' => $user->id,
                'status' => 'open',
            ]);
        });

        return response()->json(['message' => 'Shift berhasil dibuka.', 'shift' => $shift], 201);
    }

    /**
     * Close the user's currently open shift, reconciling the physically
     * counted cash against the system-expected cash (see Shift model's
     * expected_cash / variance accessors).
     */
    public function close(CloseShiftRequest $request, CashDrawerService $cashDrawer): JsonResponse
    {
        $shift = Auth::user()->shifts()->open()->latest('start_time')->first();

        if (! $shift) {
            return response()->json(['message' => 'Tidak ada shift aktif untuk ditutup.'], 422);
        }

        $closed = $cashDrawer->closeShift($shift, (float) $request->validated('actual_cash'));
        return response()->json([
            'message' => 'Shift masuk antrean review admin.',
            'shift' => array_merge($closed->toArray(), [
                'expected_cash' => $cashDrawer->expected($closed),
                'variance' => (float) $closed->cash_difference,
            ]),
            'expected_cash' => $cashDrawer->expected($closed),
            'variance' => (float) $closed->cash_difference,
        ]);
    }
}
