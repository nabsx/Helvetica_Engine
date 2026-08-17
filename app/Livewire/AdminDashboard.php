<?php

namespace App\Livewire;

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

    public function mount(): void
    {
        $this->tanggal = now(DashboardService::OPERATIONAL_TIMEZONE)->toDateString();
    }

    public function updatedPeriod(): void
    {
        if ($this->period === 'today') {
            $this->tanggal = now(DashboardService::OPERATIONAL_TIMEZONE)->toDateString();
        }
    }

    public function refreshDashboard(): void
    {
        $this->dispatch('dashboard-refreshed');
    }

    public function render(): View
    {
        return view('livewire.admin-dashboard', app(DashboardService::class)->snapshot($this->tanggal));
    }
}
