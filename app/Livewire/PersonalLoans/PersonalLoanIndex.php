<?php

namespace App\Livewire\PersonalLoans;

use App\Models\PersonalLoan;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class PersonalLoanIndex extends Component
{
    use WithPagination;

    #[Url(as: 'q', history: true)]
    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function toggleReturned(PersonalLoan $personalLoan): void
    {
        abort_unless($personalLoan->user_id === auth()->id(), 403);

        $personalLoan->update(['is_returned' => ! $personalLoan->is_returned]);

        $this->dispatch('notify', message: $personalLoan->is_returned
            ? 'Marcado como devuelto.'
            : 'Marcado como pendiente.');
    }

    public function delete(PersonalLoan $personalLoan): void
    {
        abort_unless($personalLoan->user_id === auth()->id(), 403);

        $personalLoan->delete();

        session()->flash('success', 'Préstamo personal eliminado.');
    }

    public function render()
    {
        $loans = PersonalLoan::query()
            ->where('user_id', auth()->id())
            ->when($this->search !== '', function ($query) {
                $query->where('person_name', 'like', "%{$this->search}%");
            })
            ->orderByDesc('loan_date')
            ->paginate(10);

        return view('livewire.personal-loans.personal-loan-index', [
            'loans' => $loans,
            'totalPendiente' => PersonalLoan::query()
                ->where('user_id', auth()->id())
                ->where('is_returned', false)
                ->sum('amount'),
        ]);
    }
}
