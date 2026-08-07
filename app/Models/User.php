<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'name',
        'pin',
        'role',
    ];

    // Never leak the PIN hash in JSON responses.
    protected $hidden = [
        'pin',
        'remember_token',
    ];

    /**
     * Mutator: any time `pin` is set (mass assignment or direct), it's
     * hashed automatically. Callers always pass the plain 4-6 digit PIN.
     */
    public function setPinAttribute(string $value): void
    {
        $this->attributes['pin'] = Hash::make($value);
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /** The single open shift for this user, if one exists. */
    public function activeShift(): HasOne
    {
        return $this->hasOne(Shift::class)->where('status', 'open')->latestOfMany('start_time');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isCashier(): bool
    {
        return $this->role === 'cashier';
    }

    public function isBarista(): bool
    {
        return $this->role === 'barista';
    }
}
