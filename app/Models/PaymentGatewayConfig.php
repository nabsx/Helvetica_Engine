<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentGatewayConfig extends Model
{
    use HasFactory;

    protected $fillable = ['payment_type', 'fee_rate', 'fee_flat', 'fee_mode', 'fee_basis', 'is_active'];

    protected $casts = [
        'fee_rate' => 'decimal:4',
        'fee_flat' => 'decimal:2',
        'is_active' => 'boolean',
    ];
}
