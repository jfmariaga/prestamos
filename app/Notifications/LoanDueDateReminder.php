<?php

namespace App\Notifications;

use App\Models\Loan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoanDueDateReminder extends Notification
{
    use Queueable;

    public function __construct(
        public Loan $loan,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $concepto = $this->loan->hasCommitmentDate() && ! $this->loan->hasBrokenCommitment()
            ? 'Fecha de compromiso de pago'
            : 'Pago de interés';

        return (new MailMessage)
            ->subject("Recordatorio de pago — {$this->loan->borrower->name}")
            ->greeting('Recordatorio de préstamo')
            ->line('El siguiente préstamo tiene un pago próximo a vencer. A continuación el detalle:')
            ->line(
                "| Concepto | Detalle |\n".
                "| :--- | :--- |\n".
                "| Deudor | {$this->loan->borrower->name} |\n".
                "| Teléfono | {$this->loan->borrower->phone} |\n".
                "| {$concepto} | {$this->loan->next_interest_due_date->format('d/m/Y')} |\n".
                '| Saldo pendiente | $'.number_format($this->loan->totalOutstanding(), 2).' |'
            )
            ->action('Ver préstamo', route('loans.show', $this->loan))
            ->line('Este es un recordatorio automático generado por el sistema. No es necesario responder a este correo.');
    }
}
