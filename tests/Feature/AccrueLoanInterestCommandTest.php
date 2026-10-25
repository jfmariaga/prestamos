<?php

namespace Tests\Feature;

use App\Models\Borrower;
use App\Models\Loan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccrueLoanInterestCommandTest extends TestCase
{
    use RefreshDatabase;

    private function makeLoan(string $nextInterestDueDate): Loan
    {
        return Loan::create([
            'borrower_id' => Borrower::factory()->create()->id,
            'principal_amount' => 100,
            'outstanding_principal' => 100,
            'interest_rate' => 20,
            'outstanding_interest' => 0,
            'loan_date' => now()->subMonth()->toDateString(),
            'next_interest_due_date' => $nextInterestDueDate,
            'status' => Loan::STATUS_ACTIVE,
        ]);
    }

    public function test_interest_does_not_accrue_on_the_due_date_itself(): void
    {
        $loan = $this->makeLoan(now()->toDateString());

        $this->artisan('loans:accrue-interest');

        $this->assertSame('0.00', $loan->refresh()->outstanding_interest);
        $this->assertTrue($loan->next_interest_due_date->isToday());
    }

    public function test_interest_accrues_once_the_due_date_has_passed(): void
    {
        $previousDueDate = now()->subDay();
        $loan = $this->makeLoan($previousDueDate->toDateString());

        $this->artisan('loans:accrue-interest');

        $this->assertSame('20.00', $loan->refresh()->outstanding_interest);
        $this->assertTrue($loan->next_interest_due_date->isSameDay($previousDueDate->copy()->addMonth()));
    }

    public function test_unpaid_commitment_date_loan_is_flagged_as_broken_and_switches_to_monthly_interest(): void
    {
        $commitmentDate = now()->subDay();

        $loan = Loan::create([
            'borrower_id' => Borrower::factory()->create()->id,
            'principal_amount' => 100,
            'outstanding_principal' => 100,
            'interest_rate' => 20,
            'outstanding_interest' => 15,
            'loan_date' => $commitmentDate->copy()->subDays(8)->toDateString(),
            'commitment_date' => $commitmentDate->toDateString(),
            'agreed_interest_amount' => 15,
            'next_interest_due_date' => $commitmentDate->toDateString(),
            'status' => Loan::STATUS_ACTIVE,
        ]);

        $this->artisan('loans:accrue-interest');

        $loan->refresh();

        $this->assertTrue($loan->hasBrokenCommitment());
        $this->assertTrue($loan->commitment_broken_at->isToday());
        $this->assertSame('35.00', $loan->outstanding_interest);
        $this->assertTrue($loan->next_interest_due_date->isSameDay($commitmentDate->copy()->addMonth()));
    }

    public function test_commitment_date_loan_is_not_flagged_before_the_commitment_date_passes(): void
    {
        $loan = Loan::create([
            'borrower_id' => Borrower::factory()->create()->id,
            'principal_amount' => 100,
            'outstanding_principal' => 100,
            'interest_rate' => 20,
            'outstanding_interest' => 15,
            'loan_date' => now()->toDateString(),
            'commitment_date' => now()->addDays(8)->toDateString(),
            'agreed_interest_amount' => 15,
            'next_interest_due_date' => now()->addDays(8)->toDateString(),
            'status' => Loan::STATUS_ACTIVE,
        ]);

        $this->artisan('loans:accrue-interest');

        $loan->refresh();

        $this->assertFalse($loan->hasBrokenCommitment());
        $this->assertSame('15.00', $loan->outstanding_interest);
    }
}
