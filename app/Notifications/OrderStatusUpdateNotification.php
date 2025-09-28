<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusUpdateNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private Order $order, private string $oldStatus) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('تحديث حالة الطلب - '.$this->order->order_number)
            ->greeting('مرحباً '.$notifiable->name);

        switch ($this->order->status) {
            case 'processing':
                $message->line('تم بدء معالجة طلبك!')
                    ->line('رقم الطلب: '.$this->order->order_number);
                break;
            case 'shipped':
                $message->line('تم شحن طلبك!')
                    ->line('رقم الطلب: '.$this->order->order_number)
                    ->line('تاريخ الشحن: '.$this->order->shipped_at->format('Y-m-d H:i'));
                break;
            case 'delivered':
                $message->line('تم تسليم طلبك بنجاح!')
                    ->line('رقم الطلب: '.$this->order->order_number)
                    ->line('تاريخ التسليم: '.$this->order->delivered_at->format('Y-m-d H:i'));
                break;
            case 'cancelled':
                $message->line('تم إلغاء طلبك')
                    ->line('رقم الطلب: '.$this->order->order_number);
                break;
        }

        return $message
            ->action('عرض تفاصيل الطلب', route('orders.show', $this->order->id))
            ->line('شكراً لك!');
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'order_status_update',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'old_status' => $this->oldStatus,
            'new_status' => $this->order->status,
        ];
    }
}
