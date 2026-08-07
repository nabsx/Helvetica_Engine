<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureShiftIsOpen
{
    /**
     * Block any transaction unless the logged-in user currently has an
     * open shift. This is enforced server-side (not just hidden in the
     * UI) so the rule can never be bypassed by tampering with the
     * frontend or calling the API directly.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        $activeShift = $user?->shifts()->open()->latest('start_time')->first();

        if (! $activeShift) {
            return response()->json([
                'message' => 'Tidak ada shift aktif. Silakan buka shift terlebih dahulu sebelum bertransaksi.',
            ], 403);
        }

        // Stash it so the controller doesn't need to re-query.
        $request->attributes->set('active_shift', $activeShift);

        return $next($request);
    }
}
