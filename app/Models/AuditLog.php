<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property string $event
 * @property string $auditable_type
 * @property int $auditable_id
 * @property int|null $user_id
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property array<string, mixed>|null $old_values
 * @property array<string, mixed>|null $new_values
 * @property array<string, mixed>|null $metadata
 * @property string|null $url
 * @property string|null $method
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 * @property-read \Illuminate\Database\Eloquent\Model|null $auditable
 */
class AuditLog extends Model
{
    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory> */
    use HasFactory;

    protected $fillable = [
        'event',
        'auditable_type',
        'auditable_id',
        'user_id',
        'ip_address',
        'user_agent',
        'old_values',
        'new_values',
        'metadata',
        'url',
        'method',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Get the user who performed the action.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the auditable model.
     *
     * @return MorphTo<Model, $this>
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope for specific events.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<AuditLog>  $query
     * @return \Illuminate\Database\Eloquent\Builder<AuditLog>
     */
    public function scopeEvent(\Illuminate\Database\Eloquent\Builder $query, string $event): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('event', $event);
    }

    /**
     * Scope for specific user.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<AuditLog>  $query
     * @return \Illuminate\Database\Eloquent\Builder<AuditLog>
     */
    public function scopeForUser(\Illuminate\Database\Eloquent\Builder $query, int $userId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for specific model type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<AuditLog>  $query
     * @return \Illuminate\Database\Eloquent\Builder<AuditLog>
     */
    public function scopeForModel(\Illuminate\Database\Eloquent\Builder $query, string $modelType): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('auditable_type', $modelType);
    }

    /**
     * Scope for date range.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<AuditLog>  $query
     * @param  \Carbon\Carbon|string  $startDate
     * @param  \Carbon\Carbon|string  $endDate
     * @return \Illuminate\Database\Eloquent\Builder<AuditLog>
     */
    public function scopeDateRange(\Illuminate\Database\Eloquent\Builder $query, $startDate, $endDate): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Get formatted event name.
     */
    public function getFormattedEventAttribute(): string
    {
        return ucfirst(str_replace('_', ' ', $this->event));
    }

    /**
     * Get changes summary.
     */
    public function getChangesSummaryAttribute(): string
    {
        if (! $this->old_values || ! $this->new_values) {
            return 'No changes recorded';
        }

        $changes = [];
        foreach ($this->new_values as $key => $newValue) {
            $oldValue = $this->old_values[$key] ?? null;
            if ($oldValue !== $newValue) {
                $oldValueStr = is_string($oldValue) ? $oldValue : (is_scalar($oldValue) ? (string) $oldValue : 'null');
                $newValueStr = is_string($newValue) ? $newValue : (is_scalar($newValue) ? (string) $newValue : 'null');
                $changes[] = "{$key}: {$oldValueStr} → {$newValueStr}";
            }
        }

        return implode(', ', $changes);
    }
}
