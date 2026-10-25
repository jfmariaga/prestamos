<?php

namespace Tests\Feature;

use App\Livewire\Dashboard;
use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_changing_the_date_range_updates_the_rendered_earnings(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $loan = Loan::factory()->create([
            'principal_amount' => 777,
            'outstanding_principal' => 777,
            'interest_rate' => 13,
            'outstanding_interest' => 101.01,
        ]);

        LoanPayment::create([
            'loan_id' => $loan->id,
            'paid_at' => now()->subMonths(2)->toDateString(),
            'total_amount' => 63.63,
            'interest_amount' => 63.63,
            'principal_amount' => 0,
            'resulting_outstanding_principal' => $loan->outstanding_principal,
            'resulting_outstanding_interest' => $loan->outstanding_interest,
        ]);

        $component = Livewire::test(Dashboard::class);

        $component->assertDontSee('$63.63');

        $component
            ->set('rangeStart', now()->subMonths(2)->startOfMonth()->toDateString())
            ->set('rangeEnd', now()->subMonths(2)->endOfMonth()->toDateString())
            ->assertSee('$63.63');
    }
}
