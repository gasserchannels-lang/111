<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PriceAlert;
use App\Models\Product;
use App\Models\User;
use App\Notifications\PriceDropNotification;
use App\Notifications\ProductAddedNotification;
use App\Notifications\ReviewNotification;
use App\Notifications\SystemNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class NotificationService
{
    private AuditService $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * Send price drop notification.
     */
    public function sendPriceDropNotification(Product $product, float $oldPrice, float $newPrice): void
    {
        try {
            // Get users with price alerts for this product
            $priceAlerts = PriceAlert::where('product_id', $product->id)
                ->where('is_active', true)
                ->where('target_price', '>=', $newPrice)
                ->with('user')
                ->get();

            foreach ($priceAlerts as $alert) {
                $user = $alert->user;

                if ($user && $user->email) {
                    // Send email notification
                    $user->notify(new PriceDropNotification($product, $oldPrice, $newPrice, $alert->target_price));

                    // Log the notification
                    $this->auditService->logSensitiveOperation('price_drop_notification', $user, [
                        'product_id' => $product->id,
                        'old_price' => $oldPrice,
                        'new_price' => $newPrice,
                        'target_price' => $alert->target_price,
                    ]);
                }
            }

            Log::info('Price drop notifications sent', [
                'product_id' => $product->id,
                'old_price' => $oldPrice,
                'new_price' => $newPrice,
                'alerts_count' => $priceAlerts->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send price drop notifications', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Send product added notification to admins.
     */
    public function sendProductAddedNotification(Product $product): void
    {
        try {
            $admins = User::where('is_admin', true)->get();

            foreach ($admins as $admin) {
                $admin->notify(new ProductAddedNotification($product));
            }

            Log::info('Product added notifications sent to admins', [
                'product_id' => $product->id,
                'admins_count' => $admins->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send product added notifications', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send review notification to product owner.
     */
    public function sendReviewNotification(Product $product, User $reviewer, int $rating): void
    {
        try {
            // Get product owner (if it's a store product)
            $store = $product->store;

            if ($store && $store->contact_email) {
                // Send email to store
                Mail::to($store->contact_email)->send(new ReviewNotification($product, $reviewer, $rating));
            }

            // Notify admins
            $admins = User::where('is_admin', true)->get();
            foreach ($admins as $admin) {
                $admin->notify(new ReviewNotification($product, $reviewer, $rating));
            }

            Log::info('Review notifications sent', [
                'product_id' => $product->id,
                'reviewer_id' => $reviewer->id,
                'rating' => $rating,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send review notifications', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send system notification to users.
     */
    public function sendSystemNotification(string $title, string $message, array $userIds = []): void
    {
        try {
            $users = empty($userIds) ? User::all() : User::whereIn('id', $userIds)->get();

            foreach ($users as $user) {
                $user->notify(new SystemNotification($title, $message));
            }

            Log::info('System notifications sent', [
                'title' => $title,
                'users_count' => $users->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send system notifications', [
                'title' => $title,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send welcome notification to new user.
     */
    public function sendWelcomeNotification(User $user): void
    {
        try {
            $user->notify(new SystemNotification(
                'Welcome to COPRRA!',
                'Thank you for joining COPRRA. Start comparing prices and finding the best deals!'
            ));

            Log::info('Welcome notification sent', [
                'user_id' => $user->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send welcome notification', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send price alert confirmation.
     */
    public function sendPriceAlertConfirmation(PriceAlert $alert): void
    {
        try {
            $user = $alert->user;

            if ($user && $user->email) {
                $user->notify(new SystemNotification(
                    'Price Alert Created',
                    "We'll notify you when the price of {$alert->product->name} drops to {$alert->target_price} or below."
                ));
            }

            Log::info('Price alert confirmation sent', [
                'alert_id' => $alert->id,
                'user_id' => $user->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send price alert confirmation', [
                'alert_id' => $alert->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send daily price summary.
     */
    public function sendDailyPriceSummary(User $user): void
    {
        try {
            // Get user's price alerts
            $alerts = PriceAlert::where('user_id', $user->id)
                ->where('is_active', true)
                ->with('product')
                ->get();

            if ($alerts->isEmpty()) {
                return;
            }

            // Get price changes for the day
            $priceChanges = [];
            foreach ($alerts as $alert) {
                $product = $alert->product;
                $todayOffers = $product->priceOffers()
                    ->whereDate('created_at', today())
                    ->orderBy('price')
                    ->first();

                if ($todayOffers) {
                    $priceChanges[] = [
                        'product' => $product,
                        'current_price' => $todayOffers->price,
                        'target_price' => $alert->target_price,
                        'savings' => $todayOffers->price - $alert->target_price,
                    ];
                }
            }

            if (! empty($priceChanges)) {
                $user->notify(new SystemNotification(
                    'Daily Price Summary',
                    'Here are the latest price updates for your watched products.'
                ));
            }
        } catch (\Exception $e) {
            Log::error('Failed to send daily price summary', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(string $notificationId, User $user): bool
    {
        try {
            $notification = $user->notifications()->find($notificationId);

            if ($notification && ! $notification->read_at) {
                $notification->markAsRead();

                Log::info('Notification marked as read', [
                    'notification_id' => $notificationId,
                    'user_id' => $user->id,
                ]);

                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Failed to mark notification as read', [
                'notification_id' => $notificationId,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Mark all notifications as read for user.
     */
    public function markAllAsRead(User $user): int
    {
        try {
            $count = $user->unreadNotifications()->update(['read_at' => now()]);

            Log::info('All notifications marked as read', [
                'user_id' => $user->id,
                'count' => $count,
            ]);

            return $count;
        } catch (\Exception $e) {
            Log::error('Failed to mark all notifications as read', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return 0;
        }
    }
}
