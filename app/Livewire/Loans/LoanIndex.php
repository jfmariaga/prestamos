<?php

namespace App\Livewire\Loans;

use App\Models\Loan;
use Livewire\Component;
use Livewire\WithPagination;

class LoanIndex extends Component
{
    use WithPagination;

    public function render()
    {
        $loans = Loan::query()
            ->with(['user', 'borrower'])
            ->orderByDesc('loan_date')
            ->paginate(10);

        return view('livewire.loans.loan-index', [
            'loans' => $loans,
        ]);
    }
}
