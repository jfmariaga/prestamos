<?php

namespace App\Livewire\Reports;

use App\Models\LoanPayment;
use Illuminate\Support\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class InterestCollectedList extends Component
{
    use WithPagination;

    public string $start;
    public string $end;

    public function mount(string $start, string $end): void
    {
        $this->start = $start;
        $this->end = $end;
    }

    public function render()
    {
        $query = LoanPayment::query()
            ->where('interest_amount', '>', 0)
            ->whereBetween('paid_at', [
                Carbon::parse($this->start)->startOfDay(),
                Carbon::parse($this->end)->endOfDay(),
            ]);

        $total = (float) $query->clone()->sum('interest_amount');

        $payments = $query->with('loan.borrower')
            ->orderByDesc('paid_at')
            ->paginate(15);

        return view('livewire.reports.interest-collected-list', [
            'payments' => $payments,
            'total' => $total,
        ]);
    }
}
