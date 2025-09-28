<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ReviewFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $product_id
 * @property string $title
 * @property string $content
 * @property int $rating
 * @property bool $is_verified_purchase
 * @property bool $is_approved
 * @property array<string, mixed> $helpful_votes
 * @property int $helpful_count
 * @property User $user
 * @property Product $product
 *
 * @method static ReviewFactory factory(...$parameters)
 *
 * @mixin \Eloquent
 */
class Review extends Model
{
    /** @phpstan-ignore-next-line */
    use HasFactory;

    /**
     * @var class-string<\Illuminate\Database\Eloquent\Factories\Factory<Review>>
     */
    protected static $factory = \Database\Factories\ReviewFactory::class;

    /**
     * @var list<string>
     */
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
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Product, $this>
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
