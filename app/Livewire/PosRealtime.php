<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Order;
use App\Models\Shift;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PosRealtime extends Component
{
    public ?int $shiftId = null;

    public function mount(?int $shiftId = null): void
    {
        $this->shiftId = $shiftId;
    }

    public function refreshData(): void
    {
        $categories = Category::with(['products' => fn ($query) => $query->available()])
            ->whereHas('products', fn ($query) => $query->available())
            ->get()
            ->toArray();

        $this->dispatch('pos-data-refreshed', categories: $categories);
    }

    public function render()
    {
        $shift = $this->shiftId ? Shift::find($this->shiftId) : Auth::user()?->shifts()->open()->latest('start_time')->first();
        $orders = $shift?->orders()->latest()->limit(20)->get(['id', 'order_number', 'total_amount', 'status', 'created_at']) ?? collect();

        return view('livewire.pos-realtime', compact('orders'));
    }
}
