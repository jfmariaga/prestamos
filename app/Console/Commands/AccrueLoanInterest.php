<?php

namespace App\Console\Commands;

use App\Models\Loan;
use App\Services\LoanPaymentService;
use Illuminate\Console\Command;

class AccrueLoanInterest extends Command
{
    protected $signature = 'loans:accrue-interest';

    protected $description = 'Genera el interés mensual sobre el capital pendiente de los préstamos activos cuyo corte ya venció';

    public function handle(LoanPaymentService $service): int
    {
        $loans = Loan::query()
            ->where('status', Loan::STATUS_ACTIVE)
            ->whereDate('next_interest_due_date', '<', now()->toDateString())
            ->get();

        foreach ($loans as $loan) {
            if ($loan->hasCommitmentDate() && ! $loan->hasBrokenCommitment()) {
                $loan->commitment_broken_at = now();
                $loan->save();
            }

            $service->accrueMonthlyInterest($loan);
        }

        $this->info("Interés generado para {$loans->count()} préstamo(s).");

        return self::SUCCESS;
    }
}
