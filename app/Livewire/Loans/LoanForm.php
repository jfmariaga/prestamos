<?php

namespace App\Livewire\Loans;

use App\Models\Borrower;
use App\Models\Loan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class LoanForm extends Component
{
    public string $borrower_phone = '';
    public string $borrower_name = '';
    public string $borrower_reference = '';
    public string $principal_amount = '';
    public string $interest_rate = '';
    public string $loan_date = '';

    public bool $has_commitment_date = false;
    public string $commitment_date = '';
    public string $agreed_interest_amount = '';

    public ?Borrower $existingBorrower = null;

    public function mount(): void
    {
        $this->loan_date = now()->toDateString();
    }

    public function updatedBorrowerPhone(): void
    {
        $this->existingBorrower = Borrower::query()
            ->where('phone', $this->borrower_phone)
            ->withCount('loans')
            ->first();

        if ($this->existingBorrower) {
            $this->borrower_name = $this->existingBorrower->name;
            $this->borrower_reference = $this->existingBorrower->reference ?? '';
        }
    }

    protected function rules(): array
    {
        $rules = [
            'borrower_phone' => ['required', 'string', 'max:50'],
            'borrower_name' => ['required', 'string', 'max:255'],
            'borrower_reference' => ['required', 'string', 'max:255'],
            'principal_amount' => ['required', 'numeric', 'min:0.01'],
            'interest_rate' => ['required', 'numeric', 'min:0'],
            'loan_date' => ['required', 'date'],
        ];

        if ($this->has_commitment_date) {
            $rules['commitment_date'] = ['required', 'date', 'after:loan_date'];
            $rules['agreed_interest_amount'] = ['required', 'numeric', 'min:0'];
        }

        return $rules;
    }

    public function save()
    {
        $data = $this->validate();

        $principal = (float) $data['principal_amount'];
        $rate = (float) $data['interest_rate'];
        $loanDate = Carbon::parse($data['loan_date']);

        if ($this->has_commitment_date) {
            $commitmentDate = Carbon::parse($data['commitment_date']);
            $outstandingInterest = round((float) $data['agreed_interest_amount'], 2);
        } else {
            $commitmentDate = null;
            $outstandingInterest = round($principal * ($rate / 100), 2);
        }

        $borrower = Borrower::updateOrCreate(
            ['phone' => $data['borrower_phone']],
            ['name' => $data['borrower_name'], 'reference' => $data['borrower_reference']],
        );

        $loan = Loan::create([
            'user_id' => Auth::id(),
            'borrower_id' => $borrower->id,
            'principal_amount' => $principal,
            'outstanding_principal' => $principal,
            'interest_rate' => $rate,
            'outstanding_interest' => $outstandingInterest,
            'loan_date' => $loanDate->toDateString(),
            'commitment_date' => $commitmentDate?->toDateString(),
            'agreed_interest_amount' => $this->has_commitment_date ? round((float) $data['agreed_interest_amount'], 2) : null,
            'next_interest_due_date' => $commitmentDate
                ? $commitmentDate->toDateString()
                : $loanDate->copy()->addMonth()->toDateString(),
            'status' => Loan::STATUS_ACTIVE,
        ]);

        session()->flash('success', 'Préstamo creado correctamente.');

        return redirect()->route('loans.show', $loan);
    }

    public function render()
    {
        return view('livewire.loans.loan-form');
    }
}
