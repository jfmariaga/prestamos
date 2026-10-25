<?php

namespace Tests\Feature;

use App\Models\Borrower;
use App\Models\Loan;
use App\Services\LoanPaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class LoanPaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    private function makeLoan(float $outstandingPrincipal, float $outstandingInterest, float $rate = 20): Loan
    {
        return Loan::create([
            'borrower_id' => Borrower::factory()->create()->id,
            'principal_amount' => $outstandingPrincipal,
            'outstanding_principal' => $outstandingPrincipal,
            'interest_rate' => $rate,
            'outstanding_interest' => $outstandingInterest,
            'loan_date' => now()->subMonth()->toDateString(),
            'next_interest_due_date' => now()->toDateString(),
            'status' => Loan::STATUS_ACTIVE,
        ]);
    }

    public function test_payment_settles_interest_before_principal(): void
    {
        $loan = $this->makeLoan(outstandingPrincipal: 100, outstandingInterest: 20);

        $payment = app(LoanPaymentService::class)->applyPayment($loan, 50);

        $this->assertSame('20.00', $payment->interest_amount);
        $this->assertSame('30.00', $payment->principal_amount);
        $this->assertSame('70.00', $loan->refresh()->outstanding_principal);
        $this->assertSame('0.00', $loan->outstanding_interest);
    }

    public function test_partial_payment_smaller_than_interest_only_reduces_interest(): void
    {
        $loan = $this->makeLoan(outstandingPrincipal: 100, outstandingInterest: 20);

        app(LoanPaymentService::class)->applyPayment($loan, 15);

        $loan->refresh();

        $this->assertSame('5.00', $loan->outstanding_interest);
        $this->assertSame('100.00', $loan->outstanding_principal);
    }

    public function test_full_payment_marks_loan_as_paid(): void
    {
        $loan = $this->makeLoan(outstandingPrincipal: 100, outstandingInterest: 0);

        app(LoanPaymentService::class)->applyPayment($loan, 100);

        $loan->refresh();

        $this->assertSame('0.00', $loan->outstanding_principal);
        $this->assertSame(Loan::STATUS_PAID, $loan->status);
    }

    public function test_cannot_pay_an_already_paid_loan(): void
    {
        $loan = $this->makeLoan(outstandingPrincipal: 0, outstandingInterest: 0);
        $loan->status = Loan::STATUS_PAID;
        $loan->save();

        $this->expectException(ValidationException::class);

        app(LoanPaymentService::class)->applyPayment($loan, 10);
    }

    public function test_monthly_accrual_generates_interest_from_outstanding_principal(): void
    {
        $loan = $this->makeLoan(outstandingPrincipal: 70, outstandingInterest: 0, rate: 20);
        $previousDueDate = $loan->next_interest_due_date->copy();

        app(LoanPaymentService::class)->accrueMonthlyInterest($loan);

        $loan->refresh();

        $this->assertSame('14.00', $loan->outstanding_interest);
        $this->assertTrue($loan->next_interest_due_date->equalTo($previousDueDate->addMonth()));
    }
}
