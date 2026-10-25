<?php

namespace Tests\Feature;

use App\Livewire\Loans\LoanShow;
use App\Models\Borrower;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LoanShowComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_registering_a_payment_updates_the_loan_via_livewire(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $loan = Loan::create([
            'borrower_id' => Borrower::factory()->create()->id,
            'principal_amount' => 100,
            'outstanding_principal' => 100,
            'interest_rate' => 20,
            'outstanding_interest' => 20,
            'loan_date' => now()->subMonth(),
            'next_interest_due_date' => now(),
            'status' => Loan::STATUS_ACTIVE,
        ]);

        Livewire::test(LoanShow::class, ['loan' => $loan])
            ->set('amount', 50)
            ->call('registerPayment')
            ->assertHasNoErrors();

        $loan->refresh();

        $this->assertSame('70.00', $loan->outstanding_principal);
        $this->assertSame('0.00', $loan->outstanding_interest);
    }
}
