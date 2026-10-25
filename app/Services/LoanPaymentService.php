<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\LoanPayment;
use Illuminate\Validation\ValidationException;

class LoanPaymentService
{
    /**
     * Apply a payment to a loan, settling outstanding interest before principal.
     */
    public function applyPayment(Loan $loan, float $amount): LoanPayment
    {
        if (! $loan->isActive()) {
            throw ValidationException::withMessages([
                'amount' => 'No se pueden registrar abonos sobre un préstamo ya pagado.',
            ]);
        }

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'El monto del abono debe ser mayor a cero.',
            ]);
        }

        $outstandingInterest = (float) $loan->outstanding_interest;
        $outstandingPrincipal = (float) $loan->outstanding_principal;
        $totalOutstanding = $outstandingInterest + $outstandingPrincipal;

        $amount = min($amount, $totalOutstanding);

        $interestPortion = min($amount, $outstandingInterest);
        $principalPortion = $amount - $interestPortion;

        $newOutstandingInterest = round($outstandingInterest - $interestPortion, 2);
        $newOutstandingPrincipal = round($outstandingPrincipal - $principalPortion, 2);

        $loan->outstanding_interest = $newOutstandingInterest;
        $loan->outstanding_principal = $newOutstandingPrincipal;

        if ($newOutstandingPrincipal <= 0 && $newOutstandingInterest <= 0) {
            $loan->status = Loan::STATUS_PAID;
        }

        $loan->save();

        return $loan->payments()->create([
            'paid_at' => now()->toDateString(),
            'total_amount' => round($amount, 2),
            'interest_amount' => round($interestPortion, 2),
            'principal_amount' => round($principalPortion, 2),
            'resulting_outstanding_principal' => $newOutstandingPrincipal,
            'resulting_outstanding_interest' => $newOutstandingInterest,
        ]);
    }

    /**
     * Accrue a new month of interest on the loan's remaining principal and
     * roll the next due date forward by one month.
     */
    public function accrueMonthlyInterest(Loan $loan): void
    {
        if (! $loan->isActive()) {
            return;
        }

        $generatedInterest = round((float) $loan->outstanding_principal * ((float) $loan->interest_rate / 100), 2);

        $loan->outstanding_interest = round((float) $loan->outstanding_interest + $generatedInterest, 2);
        $loan->next_interest_due_date = $loan->next_interest_due_date->copy()->addMonth();
        $loan->save();
    }
}
