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
        'is_active',
        'repeat_alert', // ✅ الإصلاح الإلزامي
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'repeat_alert' => 'boolean', // ✅ أفضل ممارسة
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
