<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentPendingVerification extends Notification
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
            ->subject('Comprobante pendiente de verificar')
            ->line("Pedido #{$this->payment->order_id}: \${$this->payment->amount_usd} por ".$this->methodLabel())
            ->action('Ver pagos pendientes', url('/payments'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'payment',
            'tone' => 'amber',
            'label' => 'Pago',
            'title' => 'Comprobante pendiente de verificar',
            'message' => "Pedido #{$this->payment->order_id}: \${$this->payment->amount_usd} por ".$this->methodLabel(),
            'href' => '/payments',
        ];
    }

    private function methodLabel(): string
    {
        return match ($this->payment->method) {
            'pago_movil' => 'pago móvil',
            'transferencia' => 'transferencia',
            default => $this->payment->method,
        };
    }
}
