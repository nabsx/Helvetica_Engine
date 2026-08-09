<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderCancellationRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Ownership (only the cashier who booked the order, or an admin,
        // may request a cancellation) is enforced in the controller, where
        // we already have the resolved Order model to check against.
        return true;
    }

    public function rules(): array
    {
        return [
            // Required and with a minimum length on purpose: this note is
            // what the admin reads before approving, and it's what makes
            // the cancellation log useful as an audit trail later.
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'Alasan pembatalan wajib diisi.',
            'reason.min' => 'Alasan pembatalan terlalu singkat, jelaskan lebih detail.',
        ];
    }
}
