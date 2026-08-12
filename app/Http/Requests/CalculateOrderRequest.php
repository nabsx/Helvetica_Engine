<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CalculateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Any authenticated staff member may preview a total; the active-shift
        // check only matters when the order is actually submitted (see
        // OrderController::store, guarded by the 'shift.active' middleware).
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'payment_type' => ['required', 'in:CASH,QRIS'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Keranjang tidak boleh kosong.',
        ];
    }
}