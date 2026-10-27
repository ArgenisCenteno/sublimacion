<?php

namespace App\Notifications;

use App\Models\Receipt;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReceiptIssued extends Notification
{
    use Queueable;

    public function __construct(private readonly Receipt $receipt)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Recibo emitido')
            ->line("Se emitió el recibo {$this->receipt->formatted_number} por \${$this->receipt->amount_usd}.")
            ->action('Descargar recibo', url("/receipts/{$this->receipt->id}/pdf"));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'receipt',
            'tone' => 'emerald',
            'label' => 'Recibo',
            'title' => 'Recibo emitido',
            'message' => "Se emitió el recibo {$this->receipt->formatted_number} por \${$this->receipt->amount_usd}.",
            'href' => "/receipts/{$this->receipt->id}/pdf",
        ];
    }
}
