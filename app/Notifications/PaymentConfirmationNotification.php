<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentConfirmationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private Payment $payment) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('تأكيد الدفع - '.$this->payment->transaction_id)
            ->greeting('مرحباً '.$notifiable->name);

        if ($this->payment->status === 'completed') {
            $message->line('تم تأكيد دفعتك بنجاح!')
                ->line('رقم المعاملة: '.$this->payment->transaction_id)
                ->line('المبلغ: '.$this->payment->amount.' '.$this->payment->currency)
                ->line('طريقة الدفع: '.$this->payment->paymentMethod->name);
        } else {
            $message->line('فشل في معالجة دفعتك')
                ->line('رقم المعاملة: '.$this->payment->transaction_id)
                ->line('يرجى المحاولة مرة أخرى أو استخدام طريقة دفع أخرى');
        }

        return $message
            ->action('عرض تفاصيل الطلب', route('orders.show', $this->payment->order_id))
            ->line('شكراً لك!');
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'payment_confirmation',
            'payment_id' => $this->payment->id,
            'transaction_id' => $this->payment->transaction_id,
            'amount' => $this->payment->amount,
            'status' => $this->payment->status,
        ];
    }
}
