<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Reward;
use App\Models\User;
use App\Models\UserPoint;
use Illuminate\Support\Facades\DB;

class PointsService
{
    public function addPoints(User $user, int $points, string $type, string $source, ?int $orderId = null, ?string $description = null): UserPoint
    {
        return UserPoint::create([
            'user_id' => $user->id,
            'points' => $points,
            'type' => $type,
            'source' => $source,
            'order_id' => $orderId,
            'description' => $description,
            'expires_at' => $this->calculateExpirationDate($type),
        ]);
    }

    public function redeemPoints(User $user, int $points, ?string $description = null): bool
    {
        $availablePoints = $this->getAvailablePoints($user->id);

        if ($availablePoints < $points) {
            return false;
        }

        DB::transaction(function () use ($user, $points, $description) {
            $this->addPoints($user, -$points, 'redeemed', 'manual_redemption', null, $description);
        });

        return true;
    }

    public function getAvailablePoints(int $userId): int
    {
        return UserPoint::where('user_id', $userId)
            ->valid()
            ->sum('points');
    }

    public function getPointsHistory(int $userId, int $limit = 20)
    {
        return UserPoint::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function awardPurchasePoints(Order $order): void
    {
        $points = (int) ($order->total_amount * 0.01); // 1 point per dollar

        $this->addPoints(
            $order->user,
            $points,
            'earned',
            'purchase',
            $order->id,
            "Points earned for order #{$order->order_number}"
        );
    }

    public function awardReviewPoints(User $user, int $rating): void
    {
        $points = $rating * 10; // 10 points per star

        $this->addPoints(
            $user,
            $points,
            'earned',
            'review',
            null,
            "Points earned for {$rating}-star review"
        );
    }

    public function awardReferralPoints(User $referrer, User $referred): void
    {
        $this->addPoints(
            $referrer,
            100,
            'earned',
            'referral',
            null,
            "Referral bonus for inviting {$referred->name}"
        );

        $this->addPoints(
            $referred,
            50,
            'earned',
            'referral_signup',
            null,
            'Welcome bonus for being referred'
        );
    }

    public function getAvailableRewards(int $userId): array
    {
        $availablePoints = $this->getAvailablePoints($userId);

        return Reward::availableForPoints($availablePoints)
            ->orderBy('points_required')
            ->get()
            ->toArray();
    }

    public function redeemReward(User $user, int $rewardId): bool
    {
        $reward = Reward::findOrFail($rewardId);
        $availablePoints = $this->getAvailablePoints($user->id);

        if ($availablePoints < $reward->points_required) {
            return false;
        }

        return DB::transaction(function () use ($user, $reward) {
            $this->redeemPoints($user, $reward->points_required, "Redeemed reward: {$reward->name}");

            // Apply reward benefits
            $this->applyReward($user, $reward);

            return true;
        });
    }

    private function applyReward(User $user, Reward $reward): void
    {
        switch ($reward->type) {
            case 'discount':
                // Store discount in user session or create discount code
                break;
            case 'free_shipping':
                // Apply free shipping flag
                break;
            case 'gift':
                // Add gift to cart or send notification
                break;
            case 'cashback':
                // Add cashback to user account
                break;
        }
    }

    private function calculateExpirationDate(string $type): ?\DateTime
    {
        if ($type === 'earned') {
            return now()->addYear(); // Points expire after 1 year
        }

        return null; // Redeemed points don't expire
    }

    public function expireOldPoints(): int
    {
        return UserPoint::where('expires_at', '<', now())
            ->where('type', 'earned')
            ->update(['type' => 'expired']);
    }
}
