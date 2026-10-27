<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockAlert extends Notification
{
    use Queueable;

    public function __construct(private readonly string $productName, private readonly int $stock)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Stock bajo')
            ->line("{$this->productName}: quedan {$this->stock} unidades.")
            ->action('Ver inventario', url('/reportes/inventario'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'stock',
            'tone' => 'rose',
            'label' => 'Stock',
            'title' => 'Stock bajo',
            'message' => "{$this->productName}: quedan {$this->stock} unidades.",
            'href' => '/reportes/inventario',
        ];
    }
}
