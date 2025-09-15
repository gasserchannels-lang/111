<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\NotificationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string $type
 * @property string $title
 * @property string $message
 * @property array<string, mixed> $data
 * @property Carbon|null $read_at
 * @property Carbon|null $sent_at
 * @property int $priority
 * @property string $channel
 * @property string $status
 * @property array<string, mixed> $metadata
 * @property array<string> $tags
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read \App\Models\User $user
 */
class Notification extends Model
{
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): NotificationFactory
    {
        return NotificationFactory::new();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'read_at',
        'sent_at',
        'priority',
        'channel',
        'status',
        'metadata',
        'tags',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'sent_at' => 'datetime',
        'priority' => 'integer',
        'metadata' => 'array',
        'tags' => 'array',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'data',
    ];

    /**
     * Get the user that owns the notification.
     *
     * @return BelongsTo<User, Notification>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include unread notifications.
     *
     * @param  Builder<Notification>  $query
     * @return Builder<Notification>
     */
    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope a query to only include read notifications.
     *
     * @param  Builder<Notification>  $query
     * @return Builder<Notification>
     */
    public function scopeRead(Builder $query): Builder
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Scope a query to only include notifications of a given type.
     *
     * @param  Builder<Notification>  $query
     * @return Builder<Notification>
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', '=', $type);
    }

    /**
     * Scope a query to only include notifications of a given priority.
     *
     * @param  Builder<Notification>  $query
     * @return Builder<Notification>
     */
    public function scopeOfPriority(Builder $query, int $priority): Builder
    {
        return $query->where('priority', '=', $priority);
    }

    /**
     * Scope a query to only include notifications of a given status.
     *
     * @param  Builder<Notification>  $query
     * @return Builder<Notification>
     */
    public function scopeOfStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', '=', $status);
    }

    /**
     * Scope a query to only include sent notifications.
     *
     * @param  Builder<Notification>  $query
     * @return Builder<Notification>
     */
    public function scopeSent(Builder $query): Builder
    {
        return $query->whereNotNull('sent_at');
    }

    /**
     * Scope a query to only include pending notifications.
     *
     * @param  Builder<Notification>  $query
     * @return Builder<Notification>
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->whereNull('sent_at')->where('status', '=', 'pending');
    }

    /**
     * Scope a query to only include failed notifications.
     *
     * @param  Builder<Notification>  $query
     * @return Builder<Notification>
     */
    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', '=', 'failed');
    }

    /**
     * Scope a query to only include notifications for a given user.
     *
     * @param  Builder<Notification>  $query
     * @return Builder<Notification>
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', '=', $userId);
    }

    /**
     * Scope a query to only include notifications created after a given date.
     *
     * @param  Builder<Notification>  $query
     * @return Builder<Notification>
     */
    public function scopeAfter(Builder $query, Carbon $date): Builder
    {
        return $query->where('created_at', '>', $date);
    }

    /**
     * Scope a query to only include notifications created before a given date.
     *
     * @param  Builder<Notification>  $query
     * @return Builder<Notification>
     */
    public function scopeBefore(Builder $query, Carbon $date): Builder
    {
        return $query->where('created_at', '<', $date);
    }

    /**
     * Scope a query to only include notifications created between two dates.
     *
     * @param  Builder<Notification>  $query
     * @return Builder<Notification>
     */
    public function scopeBetween(Builder $query, Carbon $startDate, Carbon $endDate): Builder
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Mark the notification as read.
     */
    public function markAsRead(): bool
    {
        return $this->update(['read_at' => now()]);
    }

    /**
     * Mark the notification as unread.
     */
    public function markAsUnread(): bool
    {
        return $this->update(['read_at' => null]);
    }

    /**
     * Mark the notification as sent.
     */
    public function markAsSent(): bool
    {
        return $this->update([
            'sent_at' => now(),
            'status' => 'sent',
        ]);
    }

    /**
     * Mark the notification as failed.
     */
    public function markAsFailed(?string $reason = null): bool
    {
        $data = $this->data ?? [];
        if ($reason) {
            $data['failure_reason'] = $reason;
        }

        return $this->update([
            'status' => 'failed',
            'data' => $data,
        ]);
    }

    /**
     * Check if the notification is read.
     */
    public function isRead(): bool
    {
        return ! is_null($this->read_at);
    }

    /**
     * Check if the notification is unread.
     */
    public function isUnread(): bool
    {
        return is_null($this->read_at);
    }

    /**
     * Check if the notification is sent.
     */
    public function isSent(): bool
    {
        return ! is_null($this->sent_at);
    }

    /**
     * Check if the notification is pending.
     */
    public function isPending(): bool
    {
        return is_null($this->sent_at) && $this->status === 'pending';
    }

    /**
     * Check if the notification is failed.
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Get the notification priority level.
     */
    public function getPriorityLevel(): string
    {
        return match ($this->priority) {
            1 => 'low',
            2 => 'normal',
            3 => 'high',
            4 => 'urgent',
            default => 'normal',
        };
    }

    /**
     * Get the notification type display name.
     */
    public function getTypeDisplayName(): string
    {
        return match ($this->type) {
            'price_drop' => 'Price Drop Alert',
            'new_product' => 'New Product',
            'review' => 'Review Notification',
            'system' => 'System Notification',
            'welcome' => 'Welcome Message',
            'price_alert' => 'Price Alert',
            'maintenance' => 'Maintenance Notice',
            'security' => 'Security Alert',
            'promotion' => 'Promotion',
            'newsletter' => 'Newsletter',
            default => ucfirst(str_replace('_', ' ', $this->type)),
        };
    }

    /**
     * Get the notification channel display name.
     */
    public function getChannelDisplayName(): string
    {
        return match ($this->channel) {
            'email' => 'Email',
            'sms' => 'SMS',
            'push' => 'Push Notification',
            'in_app' => 'In-App Notification',
            'slack' => 'Slack',
            'discord' => 'Discord',
            'webhook' => 'Webhook',
            default => ucfirst($this->channel),
        };
    }

    /**
     * Get the notification status display name.
     */
    public function getStatusDisplayName(): string
    {
        return match ($this->status) {
            'pending' => 'Pending',
            'sent' => 'Sent',
            'failed' => 'Failed',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get the time since the notification was created.
     */
    public function getTimeAgo(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get the time since the notification was read.
     */
    public function getReadTimeAgo(): ?string
    {
        return $this->read_at?->diffForHumans();
    }

    /**
     * Get the time since the notification was sent.
     */
    public function getSentTimeAgo(): ?string
    {
        return $this->sent_at?->diffForHumans();
    }

    /**
     * Get the notification data with fallback values.
     */
    public function getData(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->data ?? [];
        }

        return data_get($this->data, $key, $default);
    }

    /**
     * Set notification data.
     */
    public function setData(string $key, mixed $value): bool
    {
        $data = $this->data ?? [];
        data_set($data, $key, $value);

        return $this->update(['data' => $data]);
    }

    /**
     * Get the notification icon based on type.
     */
    public function getIcon(): string
    {
        return match ($this->type) {
            'price_drop' => '💰',
            'new_product' => '🆕',
            'review' => '⭐',
            'system' => '⚙️',
            'welcome' => '👋',
            'price_alert' => '🔔',
            'maintenance' => '🔧',
            'security' => '🔒',
            'promotion' => '🎉',
            'newsletter' => '📧',
            default => '📢',
        };
    }

    /**
     * Get the notification color based on priority.
     */
    public function getColor(): string
    {
        return match ($this->priority) {
            1 => 'gray',
            2 => 'blue',
            3 => 'orange',
            4 => 'red',
            default => 'blue',
        };
    }

    /**
     * Get the notification badge text.
     */
    public function getBadgeText(): string
    {
        if ($this->isUnread()) {
            return 'New';
        }

        if ($this->isFailed()) {
            return 'Failed';
        }

        if ($this->isPending()) {
            return 'Pending';
        }

        return '';
    }

    /**
     * Get the notification summary for display.
     */
    public function getSummary(int $length = 100): string
    {
        $summary = strip_tags($this->message);

        if (strlen($summary) > $length) {
            $summary = substr($summary, 0, $length).'...';
        }

        return $summary;
    }

    /**
     * Get the notification URL if available.
     */
    public function getUrl(): ?string
    {
        return $this->getData('url');
    }

    /**
     * Get the notification action button text.
     */
    public function getActionText(): ?string
    {
        return $this->getData('action_text', 'View Details');
    }

    /**
     * Check if the notification has an action.
     */
    public function hasAction(): bool
    {
        return ! is_null($this->getUrl());
    }

    /**
     * Get the notification expiration date.
     */
    public function getExpirationDate(): ?Carbon
    {
        $expirationDays = $this->getData('expiration_days');

        if ($expirationDays) {
            return $this->created_at->addDays($expirationDays);
        }

        return null;
    }

    /**
     * Check if the notification is expired.
     */
    public function isExpired(): bool
    {
        $expirationDate = $this->getExpirationDate();

        return $expirationDate ? $expirationDate->isPast() : false;
    }

    /**
     * Get the notification retry count.
     */
    public function getRetryCount(): int
    {
        return $this->getData('retry_count', 0);
    }

    /**
     * Increment the notification retry count.
     */
    public function incrementRetryCount(): bool
    {
        $retryCount = $this->getRetryCount() + 1;

        return $this->setData('retry_count', $retryCount);
    }

    /**
     * Check if the notification can be retried.
     */
    public function canRetry(int $maxRetries = 3): bool
    {
        return $this->isFailed() && $this->getRetryCount() < $maxRetries;
    }

    /**
     * Get the notification failure reason.
     */
    public function getFailureReason(): ?string
    {
        return $this->getData('failure_reason');
    }

    /**
     * Get the notification metadata.
     *
     * @return array<string, mixed>
     */
    public function getMetadata(): array
    {
        return $this->getData('metadata', []);
    }

    /**
     * Set notification metadata.
     *
     * @param  array<string, mixed>  $metadata
     */
    public function setMetadata(array $metadata): bool
    {
        return $this->setData('metadata', $metadata);
    }

    /**
     * Get the notification tags.
     *
     * @return array<string>
     */
    public function getTags(): array
    {
        return $this->getData('tags', []);
    }

    /**
     * Set notification tags.
     *
     * @param  array<string>  $tags
     */
    public function setTags(array $tags): bool
    {
        return $this->setData('tags', $tags);
    }

    /**
     * Add a tag to the notification.
     */
    public function addTag(string $tag): bool
    {
        $tags = $this->getTags();

        if (! in_array($tag, $tags)) {
            $tags[] = $tag;

            return $this->setTags($tags);
        }

        return true;
    }

    /**
     * Remove a tag from the notification.
     */
    public function removeTag(string $tag): bool
    {
        $tags = $this->getTags();
        $tags = array_filter($tags, fn ($t) => $t !== $tag);

        return $this->setTags(array_values($tags));
    }

    /**
     * Check if the notification has a specific tag.
     */
    public function hasTag(string $tag): bool
    {
        return in_array($tag, $this->getTags());
    }
}