<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusChanged extends Notification
{
    use Queueable;

    private const STATUS_LABELS = [
        'pendiente_pago' => 'Pendiente de pago',
        'pago_parcial' => 'Pago parcial',
        'pago_confirmado' => 'Pago confirmado',
        'en_preparacion' => 'En preparación',
        'enviado' => 'Enviado',
        'entregado' => 'Entregado',
        'cancelado' => 'Cancelado',
    ];

    public function __construct(private readonly Order $order)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $label = self::STATUS_LABELS[$this->order->status] ?? $this->order->status;

        return (new MailMessage())
            ->subject('Tu pedido cambió de estado')
            ->line("Pedido #{$this->order->id} ahora está: {$label}.")
            ->action('Ver pedido', url("/pedidos/{$this->order->id}"));
    }

    public function toArray(object $notifiable): array
    {
        $label = self::STATUS_LABELS[$this->order->status] ?? $this->order->status;

        return [
            'kind' => 'order',
            'tone' => 'sky',
            'label' => 'Pedido',
            'title' => 'Tu pedido cambió de estado',
            'message' => "Pedido #{$this->order->id} ahora está: {$label}.",
            'href' => "/pedidos/{$this->order->id}",
        ];
    }
}
