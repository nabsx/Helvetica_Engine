<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Access is already gated by the 'auth' + 'shift.active' middleware.
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'payment_type' => ['required', 'in:CASH,QRIS'],
            'cash_given' => ['required_if:payment_type,CASH', 'nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Keranjang tidak boleh kosong.',
            'cash_given.required_if' => 'Nominal uang tunai wajib diisi untuk pembayaran CASH.',
        ];
    }
}
