<?php

namespace Tests\Feature;

use App\Models\Borrower;
use App\Models\Loan;
use App\Models\User;
use App\Models\WhatsappNotificationLog;
use App\Notifications\LoanDueDateReminder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotifyUpcomingLoanDueDatesCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_sends_an_email_reminder_for_loans_due_within_the_configured_window(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $loan = Loan::create([
            'borrower_id' => Borrower::factory()->create()->id,
            'principal_amount' => 100,
            'outstanding_principal' => 100,
            'interest_rate' => 20,
            'outstanding_interest' => 20,
            'loan_date' => now()->toDateString(),
            'next_interest_due_date' => now()->addDays(2)->toDateString(),
            'status' => Loan::STATUS_ACTIVE,
        ]);

        $this->artisan('loans:notify-upcoming-due-dates');

        Notification::assertSentTo($user, LoanDueDateReminder::class, function ($notification) use ($loan) {
            return $notification->loan->is($loan);
        });

        $this->assertSame(1, WhatsappNotificationLog::where('loan_id', $loan->id)->count());
    }

    public function test_does_not_notify_twice_for_the_same_due_date(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $loan = Loan::create([
            'borrower_id' => Borrower::factory()->create()->id,
            'principal_amount' => 100,
            'outstanding_principal' => 100,
            'interest_rate' => 20,
            'outstanding_interest' => 20,
            'loan_date' => now()->toDateString(),
            'next_interest_due_date' => now()->addDays(2)->toDateString(),
            'status' => Loan::STATUS_ACTIVE,
        ]);

        $this->artisan('loans:notify-upcoming-due-dates');
        $this->artisan('loans:notify-upcoming-due-dates');

        Notification::assertSentToTimes($user, LoanDueDateReminder::class, 1);
        $this->assertSame(1, WhatsappNotificationLog::where('loan_id', $loan->id)->count());
    }
}
