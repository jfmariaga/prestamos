<?php

namespace App\Livewire\Loans;

use App\Models\Loan;
use App\Services\LoanPaymentService;
use Livewire\Component;

class LoanShow extends Component
{
    public Loan $loan;

    public string $amount = '';

    public function mount(Loan $loan): void
    {
        $this->loan = $loan;
    }

    protected function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    public function registerPayment(LoanPaymentService $service)
    {
        $this->validate();

        $service->applyPayment($this->loan, (float) $this->amount);

        $this->amount = '';
        $this->loan->refresh();

        $this->dispatch('notify', message: 'Pago registrado correctamente.');
    }

    public function render()
    {
        return view('livewire.loans.loan-show', [
            'payments' => $this->loan->payments()->orderByDesc('paid_at')->get(),
            'otherLoans' => $this->loan->borrower
                ->loans()
                ->where('id', '!=', $this->loan->id)
                ->orderByDesc('loan_date')
                ->get(),
        ]);
    }
}
