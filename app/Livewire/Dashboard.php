<?php

namespace App\Livewire;

use App\Services\DashboardMetricsService;
use Illuminate\Support\Carbon;
use Livewire\Component;

class Dashboard extends Component
{
    public string $rangeStart = '';
    public string $rangeEnd = '';

    public function mount(): void
    {
        $this->rangeStart = now()->startOfMonth()->toDateString();
        $this->rangeEnd = now()->endOfMonth()->toDateString();
    }

    public function render()
    {
        $metrics = app(DashboardMetricsService::class)->compute(
            Carbon::parse($this->rangeStart),
            Carbon::parse($this->rangeEnd),
        );

        return view('livewire.dashboard', $metrics);
    }
}
