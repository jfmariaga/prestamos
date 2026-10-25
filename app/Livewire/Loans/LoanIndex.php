<?php

namespace App\Livewire\Loans;

use App\Models\Loan;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class LoanIndex extends Component
{
    use WithPagination;

    #[Url(as: 'q', history: true)]
    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $loans = Loan::query()
            ->with(['user', 'borrower'])
            ->withCount('payments')
            ->when($this->search !== '', function ($query) {
                $search = $this->search;

                $query->whereHas('borrower', function ($borrowerQuery) use ($search) {
                    $borrowerQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('reference', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('loan_date')
            ->paginate(10);

        return view('livewire.loans.loan-index', [
            'loans' => $loans,
        ]);
    }
}
