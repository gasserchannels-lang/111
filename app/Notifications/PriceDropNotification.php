<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PriceDropNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public $product,
        public float $oldPrice,
        public float $newPrice,
        public $targetPrice
    ) {}

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Price Drop Alert: '.$this->product->name)
            ->line('The price for '.$this->product->name.' has dropped!')
            ->line('Old Price: $'.number_format($this->oldPrice, 2))
            ->line('New Price: $'.number_format($this->newPrice, 2))
            ->line('Your Target Price: $'.number_format($this->targetPrice, 2))
            ->action('View Product', url('/products/'.$this->product->id))
            ->line('Thank you for using our service!');
    }

    public function toArray($notifiable)
    {
        return [
            'product_id' => $this->product->id,
            'old_price' => $this->oldPrice,
            'new_price' => $this->newPrice,
            'target_price' => $this->targetPrice,
        ];
    }
}
