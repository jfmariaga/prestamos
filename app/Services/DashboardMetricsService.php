<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\LoanPayment;
use Illuminate\Support\Carbon;

class DashboardMetricsService
{
    public function compute(Carbon $rangeStart, Carbon $rangeEnd): array
    {
        $rangeStart = $rangeStart->copy()->startOfDay();
        $rangeEnd = $rangeEnd->copy()->endOfDay();

        $activeLoansCount = Loan::query()->where('status', Loan::STATUS_ACTIVE)->count();
        $overdueLoansCount = $this->overdueLoansQuery()->count();

        return [
            'saldoDisponibleParaPrestar' => (float) LoanPayment::query()->sum('principal_amount'),
            'capitalPrestado' => (float) Loan::query()->where('status', Loan::STATUS_ACTIVE)->sum('outstanding_principal'),
            'interesPendienteTotal' => (float) Loan::query()->where('status', Loan::STATUS_ACTIVE)->sum('outstanding_interest'),
            'interesPendienteEnMora' => (float) $this->overdueLoansQuery()->sum('outstanding_interest'),
            'interesCobradoRango' => (float) LoanPayment::query()
                ->whereBetween('paid_at', [$rangeStart, $rangeEnd])
                ->sum('interest_amount'),
            'interesPorCobrarRango' => (float) Loan::query()
                ->where('status', Loan::STATUS_ACTIVE)
                ->whereBetween('next_interest_due_date', [$rangeStart, $rangeEnd])
                ->sum('outstanding_interest'),
            'tasaMorosidad' => $activeLoansCount > 0 ? round(($overdueLoansCount / $activeLoansCount) * 100, 1) : 0.0,
            'prestamosEnMora' => $overdueLoansCount,
            'totalPrestamosActivos' => $activeLoansCount,
            'compromisosIncumplidos' => $this->brokenCommitmentsSummary(),
            'topDeudores' => $this->topBorrowers(),
            'proximosVencimientos' => $this->upcomingDueDates(),
            'tasaPromedioPonderada' => $this->weightedAverageRate(),
            'tendenciaCartera' => $this->portfolioTrend(),
            'tendenciaInteresCobrado' => $this->interestCollectedTrend(),
        ];
    }

    private function overdueLoansQuery()
    {
        return Loan::query()
            ->where('status', Loan::STATUS_ACTIVE)
            ->where('next_interest_due_date', '<', now()->toDateString());
    }

    private function brokenCommitmentsSummary(): array
    {
        $activeBroken = Loan::query()
            ->where('status', Loan::STATUS_ACTIVE)
            ->whereNotNull('commitment_broken_at')
            ->get(['outstanding_principal', 'outstanding_interest']);

        return [
            'cantidad' => $activeBroken->count(),
            'monto' => (float) $activeBroken->sum(fn ($loan) => (float) $loan->outstanding_principal + (float) $loan->outstanding_interest),
            'historicoTotal' => Loan::query()->whereNotNull('commitment_broken_at')->count(),
        ];
    }

    private function topBorrowers(int $limit = 5)
    {
        $loans = Loan::query()
            ->with('borrower')
            ->where('status', Loan::STATUS_ACTIVE)
            ->get(['borrower_id', 'outstanding_principal', 'outstanding_interest']);

        $totalPortfolio = $loans->sum(fn ($loan) => (float) $loan->outstanding_principal + (float) $loan->outstanding_interest);

        return $loans
            ->groupBy('borrower_id')
            ->map(function ($group) use ($totalPortfolio) {
                $outstanding = $group->sum(fn ($loan) => (float) $loan->outstanding_principal + (float) $loan->outstanding_interest);

                return [
                    'borrower_name' => $group->first()->borrower->name,
                    'borrower_phone' => $group->first()->borrower->phone,
                    'outstanding' => $outstanding,
                    'porcentaje' => $totalPortfolio > 0 ? round(($outstanding / $totalPortfolio) * 100, 1) : 0.0,
                ];
            })
            ->sortByDesc('outstanding')
            ->take($limit)
            ->values();
    }

    private function upcomingDueDates(int $days = 15, int $limit = 10)
    {
        return Loan::query()
            ->with('borrower')
            ->where('status', Loan::STATUS_ACTIVE)
            ->whereBetween('next_interest_due_date', [now()->toDateString(), now()->addDays($days)->toDateString()])
            ->orderBy('next_interest_due_date')
            ->limit($limit)
            ->get();
    }

    private function weightedAverageRate(): float
    {
        $loans = Loan::query()
            ->where('status', Loan::STATUS_ACTIVE)
            ->get(['interest_rate', 'outstanding_principal']);

        $totalPrincipal = $loans->sum(fn ($loan) => (float) $loan->outstanding_principal);

        if ($totalPrincipal <= 0) {
            return 0.0;
        }

        $weightedSum = $loans->sum(fn ($loan) => (float) $loan->interest_rate * (float) $loan->outstanding_principal);

        return round($weightedSum / $totalPrincipal, 2);
    }

    private function monthRange(int $months)
    {
        return collect(range($months - 1, 0))->map(fn ($i) => now()->subMonths($i)->startOfMonth());
    }

    private function portfolioTrend(int $months = 6): array
    {
        $range = $this->monthRange($months);
        $since = $range->first()->toDateString();

        $newLoans = Loan::query()->where('loan_date', '>=', $since)->get(['loan_date', 'principal_amount']);
        $recovered = LoanPayment::query()->where('paid_at', '>=', $since)->get(['paid_at', 'principal_amount']);

        $labels = [];
        $prestado = [];
        $recuperado = [];

        foreach ($range as $month) {
            $labels[] = ucfirst($month->translatedFormat('M Y'));

            $prestado[] = (float) $newLoans
                ->filter(fn ($loan) => Carbon::parse($loan->loan_date)->isSameMonth($month))
                ->sum('principal_amount');

            $recuperado[] = (float) $recovered
                ->filter(fn ($payment) => Carbon::parse($payment->paid_at)->isSameMonth($month))
                ->sum('principal_amount');
        }

        return compact('labels', 'prestado', 'recuperado');
    }

    private function interestCollectedTrend(int $months = 6): array
    {
        $range = $this->monthRange($months);
        $since = $range->first()->toDateString();

        $payments = LoanPayment::query()->where('paid_at', '>=', $since)->get(['paid_at', 'interest_amount']);

        $labels = [];
        $cobrado = [];

        foreach ($range as $month) {
            $labels[] = ucfirst($month->translatedFormat('M Y'));

            $cobrado[] = (float) $payments
                ->filter(fn ($payment) => Carbon::parse($payment->paid_at)->isSameMonth($month))
                ->sum('interest_amount');
        }

        return compact('labels', 'cobrado');
    }
}
