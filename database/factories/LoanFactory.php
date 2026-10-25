<?php

namespace Database\Factories;

use App\Models\Borrower;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Loan>
 */
class LoanFactory extends Factory
{
    public function definition(): array
    {
        $principal = fake()->randomElement([500, 1000, 1500, 2000, 3000]);
        $rate = fake()->randomElement([5, 8, 10, 12, 15]);
        $loanDate = fake()->dateTimeBetween('-3 months', 'now');

        return [
            'user_id' => User::factory(),
            'borrower_id' => Borrower::factory(),
            'principal_amount' => $principal,
            'outstanding_principal' => $principal,
            'interest_rate' => $rate,
            'outstanding_interest' => round($principal * ($rate / 100), 2),
            'loan_date' => $loanDate,
            'next_interest_due_date' => (clone $loanDate)->modify('+1 month'),
            'status' => Loan::STATUS_ACTIVE,
        ];
    }
}
