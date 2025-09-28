<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderConfirmationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private Order $order) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('تأكيد الطلب - '.$this->order->order_number)
            ->greeting('مرحباً '.$notifiable->name)
            ->line('تم تأكيد طلبك بنجاح!')
            ->line('رقم الطلب: '.$this->order->order_number)
            ->line('المبلغ الإجمالي: '.$this->order->total_amount.' '.$this->order->currency)
            ->line('حالة الطلب: '.$this->getStatusText($this->order->status))
            ->action('عرض تفاصيل الطلب', route('orders.show', $this->order->id))
            ->line('شكراً لك لاختيارك خدماتنا!');
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'order_confirmation',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'total_amount' => $this->order->total_amount,
            'status' => $this->order->status,
        ];
    }

    private function getStatusText(string $status): string
    {
        return match ($status) {
            'pending' => 'في الانتظار',
            'processing' => 'قيد المعالجة',
            'shipped' => 'تم الشحن',
            'delivered' => 'تم التسليم',
            'cancelled' => 'ملغي',
            'refunded' => 'مسترد',
            default => $status,
        };
    }
}
