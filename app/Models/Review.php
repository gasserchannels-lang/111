<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @template TFactory of \Illuminate\Database\Eloquent\Factories\Factory
 *
 * @property-read \App\Models\Product $product
 * @property-read \App\Models\User $user
 */
class Review extends Model
{
    /** @use HasFactory<\App\Models\Review> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'title',
        'content',
        'rating',
        'is_verified_purchase',
        'is_approved',
        'helpful_votes',
        'helpful_count',
    ];

    protected $casts = [
        'is_verified_purchase' => 'boolean',
        'is_approved' => 'boolean',
        'helpful_votes' => 'array',
        'helpful_count' => 'integer',
        'rating' => 'integer',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Review>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Product, \App\Models\Review>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // دالة مساعدة للتوافق مع الكود القديم
    public function getReviewTextAttribute(): string
    {
        return $this->content;
    }
}
