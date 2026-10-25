<?php

namespace App\Console\Commands;

use App\Models\Loan;
use App\Models\User;
use App\Notifications\LoanDueDateReminder;
use App\Services\Whatsapp\WhatsappNotifier;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;

class NotifyUpcomingLoanDueDates extends Command
{
    protected $signature = 'loans:notify-upcoming-due-dates';

    protected $description = 'Notifica a los usuarios sobre préstamos próximos a su fecha de pago de interés o de compromiso';

    public function handle(WhatsappNotifier $notifier): int
    {
        $daysBefore = (int) config('loans.notification_days_before', 2);
        $targetDate = Carbon::today()->addDays($daysBefore);

        $loans = Loan::query()
            ->with('borrower')
            ->where('status', Loan::STATUS_ACTIVE)
            ->whereDate('next_interest_due_date', $targetDate->toDateString())
            ->get();

        $users = User::all();

        if ($users->isEmpty()) {
            $this->warn('No hay usuarios registrados para notificar.');

            return self::SUCCESS;
        }

        $whatsappEnabled = (bool) config('loans.whatsapp.token') && (bool) config('loans.whatsapp.phone_number_id');
        $whatsappRecipients = $users->whereNotNull('whatsapp_number');

        if (! $whatsappEnabled) {
            $this->comment('WhatsApp no está configurado (faltan credenciales); se notificará solo por correo.');
        }

        $sent = 0;

        foreach ($loans as $loan) {
            $alreadySent = $loan->notificationLogs()
                ->whereDate('due_date', $loan->next_interest_due_date->toDateString())
                ->exists();

            if ($alreadySent) {
                continue;
            }

            Notification::send($users, new LoanDueDateReminder($loan));

            if ($whatsappEnabled && $whatsappRecipients->isNotEmpty()) {
                $message = sprintf(
                    'Recordatorio: el préstamo de %s (tel. %s) tiene %s el %s. Saldo pendiente: %s.',
                    $loan->borrower->name,
                    $loan->borrower->phone,
                    $loan->hasCommitmentDate() && ! $loan->hasBrokenCommitment() ? 'fecha de compromiso de pago' : 'pago de interés',
                    $loan->next_interest_due_date->format('d/m/Y'),
                    number_format($loan->totalOutstanding(), 2)
                );

                foreach ($whatsappRecipients as $recipient) {
                    $notifier->send($recipient->whatsapp_number, $message);
                }
            }

            $loan->notificationLogs()->create([
                'due_date' => $loan->next_interest_due_date->toDateString(),
                'sent_at' => now(),
            ]);

            $sent++;
        }

        $this->info("Notificaciones enviadas para {$sent} préstamo(s).");

        return self::SUCCESS;
    }
}
