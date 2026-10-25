<?php

namespace Tests\Feature;

use App\Models\Borrower;
use App\Models\Loan;
use App\Services\DashboardMetricsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DashboardMetricsServiceTest extends TestCase
{
    use RefreshDatabase;

    private function makeLoan(array $overrides = []): Loan
    {
        return Loan::create(array_merge([
            'borrower_id' => Borrower::factory()->create()->id,
            'principal_amount' => 100,
            'outstanding_principal' => 100,
            'interest_rate' => 20,
            'outstanding_interest' => 20,
            'loan_date' => now()->toDateString(),
            'next_interest_due_date' => now()->addMonth()->toDateString(),
            'status' => Loan::STATUS_ACTIVE,
        ], $overrides));
    }

    public function test_computes_portfolio_and_pending_interest_totals(): void
    {
        $this->makeLoan(['outstanding_principal' => 100, 'outstanding_interest' => 20]);
        $this->makeLoan(['outstanding_principal' => 200, 'outstanding_interest' => 40]);
        $this->makeLoan(['status' => Loan::STATUS_PAID, 'outstanding_principal' => 0, 'outstanding_interest' => 0]);

        $metrics = app(DashboardMetricsService::class)->compute(now()->startOfMonth(), now()->endOfMonth());

        $this->assertSame(300.0, $metrics['capitalPrestado']);
        $this->assertSame(60.0, $metrics['interesPendienteTotal']);
    }

    public function test_delinquency_rate_counts_only_overdue_active_loans(): void
    {
        $this->makeLoan(['next_interest_due_date' => now()->subDay()->toDateString()]);
        $this->makeLoan(['next_interest_due_date' => now()->addMonth()->toDateString()]);

        $metrics = app(DashboardMetricsService::class)->compute(now()->startOfMonth(), now()->endOfMonth());

        $this->assertSame(1, $metrics['prestamosEnMora']);
        $this->assertSame(2, $metrics['totalPrestamosActivos']);
        $this->assertSame(50.0, $metrics['tasaMorosidad']);
    }

    public function test_broken_commitments_are_summarized(): void
    {
        $this->makeLoan([
            'commitment_date' => now()->subDays(5)->toDateString(),
            'commitment_broken_at' => now(),
            'outstanding_principal' => 100,
            'outstanding_interest' => 35,
        ]);
        $this->makeLoan();

        $metrics = app(DashboardMetricsService::class)->compute(now()->startOfMonth(), now()->endOfMonth());

        $this->assertSame(1, $metrics['compromisosIncumplidos']['cantidad']);
        $this->assertSame(135.0, $metrics['compromisosIncumplidos']['monto']);
    }

    public function test_top_borrowers_are_ranked_by_outstanding_balance_with_percentage(): void
    {
        $this->makeLoan(['borrower_id' => Borrower::factory()->create(['name' => 'Grande'])->id, 'outstanding_principal' => 300, 'outstanding_interest' => 0]);
        $this->makeLoan(['borrower_id' => Borrower::factory()->create(['name' => 'Pequeño'])->id, 'outstanding_principal' => 100, 'outstanding_interest' => 0]);

        $metrics = app(DashboardMetricsService::class)->compute(now()->startOfMonth(), now()->endOfMonth());

        $top = $metrics['topDeudores'];

        $this->assertSame('Grande', $top[0]['borrower_name']);
        $this->assertSame(75.0, $top[0]['porcentaje']);
        $this->assertSame('Pequeño', $top[1]['borrower_name']);
        $this->assertSame(25.0, $top[1]['porcentaje']);
    }

    public function test_weighted_average_rate_is_weighted_by_outstanding_principal(): void
    {
        $this->makeLoan(['interest_rate' => 10, 'outstanding_principal' => 300]);
        $this->makeLoan(['interest_rate' => 30, 'outstanding_principal' => 100]);

        $metrics = app(DashboardMetricsService::class)->compute(now()->startOfMonth(), now()->endOfMonth());

        // (10*300 + 30*100) / 400 = 15
        $this->assertSame(15.0, $metrics['tasaPromedioPonderada']);
    }

    public function test_interest_due_in_range_only_counts_loans_due_within_the_range(): void
    {
        $this->makeLoan(['next_interest_due_date' => now()->addDays(3)->toDateString(), 'outstanding_interest' => 25]);
        $this->makeLoan(['next_interest_due_date' => now()->addMonths(2)->toDateString(), 'outstanding_interest' => 99]);

        $metrics = app(DashboardMetricsService::class)->compute(now(), now()->addDays(7));

        $this->assertSame(25.0, $metrics['interesPorCobrarRango']);
    }

    public function test_upcoming_due_dates_are_limited_to_the_next_fifteen_days(): void
    {
        $soon = $this->makeLoan(['next_interest_due_date' => now()->addDays(5)->toDateString()]);
        $this->makeLoan(['next_interest_due_date' => now()->addDays(30)->toDateString()]);

        $metrics = app(DashboardMetricsService::class)->compute(now()->startOfMonth(), now()->endOfMonth());

        $this->assertCount(1, $metrics['proximosVencimientos']);
        $this->assertTrue($metrics['proximosVencimientos']->first()->is($soon));
    }

    public function test_portfolio_trend_covers_the_last_six_months(): void
    {
        $metrics = app(DashboardMetricsService::class)->compute(now()->startOfMonth(), now()->endOfMonth());

        $this->assertCount(6, $metrics['tendenciaCartera']['labels']);
        $this->assertCount(6, $metrics['tendenciaCartera']['prestado']);
        $this->assertCount(6, $metrics['tendenciaCartera']['recuperado']);
    }
}
