<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOrderPlaced extends Notification
{
    use Queueable;

    public function __construct(private readonly Order $order)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Nuevo pedido recibido')
            ->line("Pedido #{$this->order->id} de {$this->order->customer_name} por \${$this->order->total_usd}.")
            ->action('Ver pedido', url("/orders/{$this->order->id}"));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'order',
            'tone' => 'sky',
            'label' => 'Pedido',
            'title' => 'Nuevo pedido recibido',
            'message' => "Pedido #{$this->order->id} de {$this->order->customer_name} por \${$this->order->total_usd}.",
            'href' => "/orders/{$this->order->id}",
        ];
    }
}
