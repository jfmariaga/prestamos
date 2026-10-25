<?php

namespace App\Livewire\Reports;

use App\Models\Loan;
use Livewire\Component;
use Livewire\WithPagination;

class OverdueLoansList extends Component
{
    use WithPagination;

    public function render()
    {
        $query = Loan::query()
            ->where('status', Loan::STATUS_ACTIVE)
            ->where('next_interest_due_date', '<', now()->toDateString());

        $total = (float) $query->clone()->sum('outstanding_interest');

        $loans = $query->with('borrower')
            ->orderBy('next_interest_due_date')
            ->paginate(15);

        return view('livewire.reports.overdue-loans-list', [
            'loans' => $loans,
            'total' => $total,
        ]);
    }
}
