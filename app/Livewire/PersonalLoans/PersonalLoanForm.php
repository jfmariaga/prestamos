<?php

namespace App\Livewire\PersonalLoans;

use App\Models\PersonalLoan;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PersonalLoanForm extends Component
{
    public ?PersonalLoan $personalLoan = null;

    public string $person_name = '';
    public string $amount = '';
    public string $loan_date = '';
    public string $notes = '';

    public function mount(?PersonalLoan $personalLoan = null): void
    {
        if (! $personalLoan || ! $personalLoan->exists) {
            $this->loan_date = now()->toDateString();

            return;
        }

        abort_unless($personalLoan->user_id === Auth::id(), 403);

        $this->personalLoan = $personalLoan;
        $this->person_name = $personalLoan->person_name;
        $this->amount = (string) $personalLoan->amount;
        $this->loan_date = $personalLoan->loan_date->toDateString();
        $this->notes = $personalLoan->notes ?? '';
    }

    protected function rules(): array
    {
        return [
            'person_name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'loan_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function save()
    {
        $data = $this->validate();

        if ($this->personalLoan) {
            $this->personalLoan->update($data);

            session()->flash('success', 'Préstamo personal actualizado correctamente.');
        } else {
            PersonalLoan::create([...$data, 'user_id' => Auth::id()]);

            session()->flash('success', 'Préstamo personal registrado correctamente.');
        }

        return redirect()->route('personal-loans.index');
    }

    public function render()
    {
        return view('livewire.personal-loans.personal-loan-form');
    }
}
