<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'target_price',
        'current_price',
        'currency',
        'is_active',
        'last_checked_at',
        'last_triggered_at',
        'trigger_count',
    ];

    protected $casts = [
        'target_price' => 'decimal:2',
        'current_price' => 'decimal:2',
        'is_active' => 'boolean',
        'last_checked_at' => 'datetime',
        'last_triggered_at' => 'datetime',
        'trigger_count' => 'integer',
    ];

    /**
     * علاقة مع المستخدم
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * علاقة مع المنتج
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * فلترة التنبيهات النشطة
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * فلترة حسب المستخدم
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * فلترة التنبيهات التي يجب فحصها
     */
    public function scopeNeedsCheck($query)
    {
        return $query->active()
            ->where(function ($q) {
                $q->whereNull('last_checked_at')
                  ->orWhere('last_checked_at', '<', now()->subMinutes(5));
            });
    }

    /**
     * التحقق من وجود تنبيه سعري للمستخدم والمنتج
     */
    public static function hasAlert(int $userId, int $productId): bool
    {
        return static::where('user_id', $userId)
            ->where('product_id', $productId)
            ->active()
            ->exists();
    }

    /**
     * إنشاء تنبيه سعري جديد
     */
    public static function createAlert(int $userId, int $productId, float $targetPrice, string $currency = 'USD'): ?self
    {
        if (static::hasAlert($userId, $productId)) {
            return null; // Alert already exists
        }

        return static::create([
            'user_id' => $userId,
            'product_id' => $productId,
            'target_price' => $targetPrice,
            'currency' => $currency,
            'is_active' => true,
        ]);
    }

    /**
     * تحديث السعر الحالي وفحص التنبيه
     */
    public function updateCurrentPrice(float $currentPrice): bool
    {
        $this->update([
            'current_price' => $currentPrice,
            'last_checked_at' => now(),
        ]);

        // Check if alert should be triggered
        if ($this->shouldTrigger($currentPrice)) {
            $this->trigger();
            return true;
        }

        return false;
    }

    /**
     * التحقق من ضرورة تشغيل التنبيه
     */
    private function shouldTrigger(float $currentPrice): bool
    {
        return $this->is_active && $currentPrice <= $this->target_price;
    }

    /**
     * تشغيل التنبيه
     */
    private function trigger(): void
    {
        $this->update([
            'last_triggered_at' => now(),
            'trigger_count' => $this->trigger_count + 1,
        ]);

        // Here you would send notification (email, SMS, etc.)
        // For now, we'll just log it
        \Log::info('Price alert triggered', [
            'user_id' => $this->user_id,
            'product_id' => $this->product_id,
            'target_price' => $this->target_price,
            'current_price' => $this->current_price,
        ]);
    }
}
