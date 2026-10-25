<?php

namespace App\Livewire\Reports;

use App\Models\Loan;
use Illuminate\Support\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class InterestDueList extends Component
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
        $query = Loan::query()
            ->where('status', Loan::STATUS_ACTIVE)
            ->whereBetween('next_interest_due_date', [
                Carbon::parse($this->start)->toDateString(),
                Carbon::parse($this->end)->toDateString(),
            ]);

        $total = (float) $query->clone()->sum('outstanding_interest');

        $loans = $query->with('borrower')
            ->orderBy('next_interest_due_date')
            ->paginate(15);

        return view('livewire.reports.interest-due-list', [
            'loans' => $loans,
            'total' => $total,
        ]);
    }
}
