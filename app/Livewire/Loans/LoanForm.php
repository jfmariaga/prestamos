<?php

namespace App\Livewire\Loans;

use App\Models\Borrower;
use App\Models\Loan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class LoanForm extends Component
{
    public ?Loan $loan = null;

    public string $borrower_phone = '';
    public string $borrower_name = '';
    public string $borrower_reference = '';
    public string $principal_amount = '';
    public string $interest_rate = '';
    public string $loan_date = '';

    public bool $has_commitment_date = false;
    public string $commitment_date = '';
    public string $agreed_interest_rate = '';

    public ?Borrower $existingBorrower = null;

    public function mount(?Loan $loan = null): void
    {
        if (! $loan || ! $loan->exists) {
            $this->loan_date = now()->toDateString();

            return;
        }

        $this->loan = $loan;
        $this->borrower_phone = $loan->borrower->phone;
        $this->borrower_name = $loan->borrower->name;
        $this->borrower_reference = $loan->borrower->reference ?? '';
        $this->principal_amount = (string) $loan->principal_amount;
        $this->interest_rate = (string) $loan->interest_rate;
        $this->loan_date = $loan->loan_date->toDateString();
        $this->has_commitment_date = $loan->hasCommitmentDate();

        if ($this->has_commitment_date) {
            $this->commitment_date = $loan->commitment_date->toDateString();
            $this->agreed_interest_rate = (float) $loan->principal_amount > 0
                ? (string) round(((float) $loan->agreed_interest_amount / (float) $loan->principal_amount) * 100, 2)
                : '';
        }
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

    public function computedAgreedInterestAmount(): ?string
    {
        if (! $this->has_commitment_date || $this->principal_amount === '' || $this->agreed_interest_rate === '') {
            return null;
        }

        $amount = ((float) $this->principal_amount) * ((float) $this->agreed_interest_rate / 100);

        return number_format($amount, 2);
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
            $rules['agreed_interest_rate'] = ['required', 'numeric', 'min:0'];
        }

        return $rules;
    }

    public function save()
    {
        if ($this->loan && $this->loan->payments()->exists()) {
            session()->flash('error', 'No se puede editar un préstamo que ya tiene abonos registrados.');

            return redirect()->route('loans.show', $this->loan);
        }

        $data = $this->validate();

        $principal = (float) $data['principal_amount'];
        $rate = (float) $data['interest_rate'];
        $loanDate = Carbon::parse($data['loan_date']);

        if ($this->has_commitment_date) {
            $commitmentDate = Carbon::parse($data['commitment_date']);
            $agreedRate = round((float) $data['agreed_interest_rate'], 2);
            $newAgreedInterestAmount = round($principal * ($agreedRate / 100), 2);
        } else {
            $commitmentDate = null;
            $newAgreedInterestAmount = null;
        }

        $newInitialInterest = $this->has_commitment_date
            ? $newAgreedInterestAmount
            : round($principal * ($rate / 100), 2);

        $borrower = Borrower::updateOrCreate(
            ['phone' => $data['borrower_phone']],
            ['name' => $data['borrower_name'], 'reference' => $data['borrower_reference']],
        );

        if ($this->loan) {
            $oldPrincipal = (float) $this->loan->principal_amount;
            $oldInitialInterest = $this->loan->hasCommitmentDate()
                ? (float) $this->loan->agreed_interest_amount
                : round($oldPrincipal * ((float) $this->loan->interest_rate / 100), 2);

            $outstandingPrincipal = max(round((float) $this->loan->outstanding_principal + ($principal - $oldPrincipal), 2), 0);
            $outstandingInterest = max(round((float) $this->loan->outstanding_interest + ($newInitialInterest - $oldInitialInterest), 2), 0);

            $oldDueDateBaseline = $this->loan->hasCommitmentDate()
                ? $this->loan->commitment_date
                : $this->loan->loan_date->copy()->addMonth();
            $newDueDateBaseline = $commitmentDate ?: $loanDate->copy()->addMonth();

            $dueDateShiftInDays = (int) round(
                ($newDueDateBaseline->copy()->startOfDay()->timestamp - $oldDueDateBaseline->copy()->startOfDay()->timestamp) / 86400
            );

            $nextInterestDueDate = $this->loan->next_interest_due_date->copy()->addDays($dueDateShiftInDays);

            $this->loan->update([
                'borrower_id' => $borrower->id,
                'principal_amount' => $principal,
                'outstanding_principal' => $outstandingPrincipal,
                'interest_rate' => $rate,
                'outstanding_interest' => $outstandingInterest,
                'loan_date' => $loanDate->toDateString(),
                'commitment_date' => $commitmentDate?->toDateString(),
                'agreed_interest_amount' => $newAgreedInterestAmount,
                'next_interest_due_date' => $nextInterestDueDate->toDateString(),
                'commitment_broken_at' => $this->has_commitment_date ? $this->loan->commitment_broken_at : null,
                'status' => ($outstandingPrincipal <= 0 && $outstandingInterest <= 0) ? Loan::STATUS_PAID : Loan::STATUS_ACTIVE,
            ]);

            session()->flash('success', 'Préstamo actualizado correctamente.');

            return redirect()->route('loans.show', $this->loan);
        }

        $loan = Loan::create([
            'user_id' => Auth::id(),
            'borrower_id' => $borrower->id,
            'principal_amount' => $principal,
            'outstanding_principal' => $principal,
            'interest_rate' => $rate,
            'outstanding_interest' => $newInitialInterest,
            'loan_date' => $loanDate->toDateString(),
            'commitment_date' => $commitmentDate?->toDateString(),
            'agreed_interest_amount' => $newAgreedInterestAmount,
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
