<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentVerified extends Notification
{
    use Queueable;

    public function __construct(private readonly Payment $payment)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Pago verificado')
            ->line("Tu pago de \${$this->payment->amount_usd} del pedido #{$this->payment->order_id} fue verificado.")
            ->action('Ver pedido', url("/pedidos/{$this->payment->order_id}"));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'payment',
            'tone' => 'emerald',
            'label' => 'Pago',
            'title' => 'Pago verificado',
            'message' => "Tu pago de \${$this->payment->amount_usd} del pedido #{$this->payment->order_id} fue verificado.",
            'href' => "/pedidos/{$this->payment->order_id}",
        ];
    }
}
