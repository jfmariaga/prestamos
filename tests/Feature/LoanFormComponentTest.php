<?php

namespace Tests\Feature;

use App\Livewire\Loans\LoanForm;
use App\Models\Borrower;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LoanFormComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_regular_loan_accrues_the_first_month_of_interest(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(LoanForm::class)
            ->set('borrower_name', 'Ana')
            ->set('borrower_phone', '3001234567')
            ->set('borrower_reference', 'Luis')
            ->set('principal_amount', '100')
            ->set('interest_rate', '20')
            ->set('loan_date', now()->toDateString())
            ->call('save')
            ->assertHasNoErrors();

        $loan = Loan::firstOrFail();

        $this->assertSame('20.00', $loan->outstanding_interest);
        $this->assertFalse($loan->hasCommitmentDate());
        $this->assertTrue($loan->next_interest_due_date->isSameDay(now()->addMonth()));
        $this->assertTrue($loan->user->is($user));
        $this->assertSame('Ana', $loan->borrower->name);
        $this->assertSame('3001234567', $loan->borrower->phone);
    }

    public function test_an_existing_borrower_is_reused_when_the_phone_number_matches(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $borrower = Borrower::factory()->create(['phone' => '3001234567', 'name' => 'Ana Original']);

        Livewire::test(LoanForm::class)
            ->set('borrower_phone', '3001234567')
            ->assertSet('borrower_name', 'Ana Original')
            ->set('principal_amount', '100')
            ->set('interest_rate', '20')
            ->set('loan_date', now()->toDateString())
            ->set('borrower_reference', $borrower->reference)
            ->call('save')
            ->assertHasNoErrors();

        $loan = Loan::firstOrFail();

        $this->assertSame(1, Borrower::count());
        $this->assertTrue($loan->borrower->is($borrower));
    }

    public function test_creating_a_commitment_date_loan_uses_the_agreed_flat_interest(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $loanDate = now();
        $commitmentDate = $loanDate->copy()->addDays(8);

        Livewire::test(LoanForm::class)
            ->set('borrower_name', 'Ana')
            ->set('borrower_phone', '3001234567')
            ->set('borrower_reference', 'Luis')
            ->set('principal_amount', '100')
            ->set('interest_rate', '20')
            ->set('loan_date', $loanDate->toDateString())
            ->set('has_commitment_date', true)
            ->set('commitment_date', $commitmentDate->toDateString())
            ->set('agreed_interest_amount', '15')
            ->call('save')
            ->assertHasNoErrors();

        $loan = Loan::firstOrFail();

        $this->assertTrue($loan->hasCommitmentDate());
        $this->assertSame('15.00', $loan->outstanding_interest);
        $this->assertSame('15.00', $loan->agreed_interest_amount);
        $this->assertTrue($loan->commitment_date->isSameDay($commitmentDate));
        $this->assertTrue($loan->next_interest_due_date->isSameDay($loan->commitment_date));
        $this->assertFalse($loan->hasBrokenCommitment());
    }

    public function test_commitment_date_loan_requires_date_and_agreed_interest(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(LoanForm::class)
            ->set('borrower_name', 'Ana')
            ->set('borrower_phone', '3001234567')
            ->set('borrower_reference', 'Luis')
            ->set('principal_amount', '100')
            ->set('interest_rate', '20')
            ->set('loan_date', now()->toDateString())
            ->set('has_commitment_date', true)
            ->call('save')
            ->assertHasErrors(['commitment_date', 'agreed_interest_amount']);
    }

    public function test_commitment_date_must_be_after_the_loan_date(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(LoanForm::class)
            ->set('borrower_name', 'Ana')
            ->set('borrower_phone', '3001234567')
            ->set('borrower_reference', 'Luis')
            ->set('principal_amount', '100')
            ->set('interest_rate', '20')
            ->set('loan_date', now()->toDateString())
            ->set('has_commitment_date', true)
            ->set('commitment_date', now()->subDay()->toDateString())
            ->set('agreed_interest_amount', '15')
            ->call('save')
            ->assertHasErrors(['commitment_date']);
    }
}
