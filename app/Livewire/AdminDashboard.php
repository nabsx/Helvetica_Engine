<?php

namespace App\Livewire;

use App\Models\Product;
use App\Services\DashboardService;
use Illuminate\View\View;
use Livewire\Component;

class AdminDashboard extends Component
{
    public string $tanggal;

    public string $period = 'today';

    protected $listeners = [
        'order-created' => '$refresh',
        'shift-updated' => '$refresh',
        'cash-movement-recorded' => '$refresh',
        'refund-approved' => '$refresh',
        'approval-updated' => '$refresh',
        'expense-created' => '$refresh',
    ];

    /** @var array<int, int> */
    public array $notifiedLowStockIds = [];

    public function mount(): void
    {
        $this->tanggal = now(DashboardService::OPERATIONAL_TIMEZONE)->toDateString();
        // Start empty so the dashboard announces current low-stock products when it hydrates.
        $this->notifiedLowStockIds = [];
    }

    public function updatedPeriod(): void
    {
        if ($this->period === 'today') {
            $this->tanggal = now(DashboardService::OPERATIONAL_TIMEZONE)->toDateString();
        }
    }

    public function refreshDashboard(): void
    {
        $currentIds = $this->currentLowStockIds();
        $this->notifiedLowStockIds = array_values(array_intersect($this->notifiedLowStockIds, $currentIds));

        $newIds = array_values(array_diff($currentIds, $this->notifiedLowStockIds));
        if ($newIds !== []) {
            $products = Product::query()
                ->whereIn('id', $newIds)
                ->orderBy('stock')
                ->get(['id', 'name', 'stock'])
                ->map(fn (Product $product): array => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'stock' => $product->stock,
                ])
                ->values()
                ->all();

            $this->notifiedLowStockIds = array_values(array_unique([...$this->notifiedLowStockIds, ...$newIds]));
            $this->dispatch('low-stock-detected', products: $products);
        }

        $this->dispatch('dashboard-refreshed');
    }

    /** @return array<int, int> */
    private function currentLowStockIds(): array
    {
        return Product::query()
            ->whereColumn('stock', '<=', 'low_stock_threshold')
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->all();
    }

    public function render(): View
    {
        return view('livewire.admin-dashboard', app(DashboardService::class)->snapshot($this->tanggal));
    }
}
