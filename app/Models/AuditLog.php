<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @template TFactory of \Illuminate\Database\Eloquent\Factories\Factory
 */
class AuditLog extends Model
{
    /** @use HasFactory<TFactory> */
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
     * @return BelongsTo<User<Database\Factories\UserFactory>, AuditLog<Database\Factories\AuditLogFactory>>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the auditable model.
     *
     * @return MorphTo<Model, AuditLog<Database\Factories\AuditLogFactory>>
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope for specific events.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<AuditLog<Database\Factories\AuditLogFactory>>  $query
     * @return \Illuminate\Database\Eloquent\Builder<AuditLog<Database\Factories\AuditLogFactory>>
     */
    public function scopeEvent($query, string $event)
    {
        return $query->where('event', $event);
    }

    /**
     * Scope for specific user.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<AuditLog<Database\Factories\AuditLogFactory>>  $query
     * @return \Illuminate\Database\Eloquent\Builder<AuditLog<Database\Factories\AuditLogFactory>>
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for specific model type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<AuditLog<Database\Factories\AuditLogFactory>>  $query
     * @return \Illuminate\Database\Eloquent\Builder<AuditLog<Database\Factories\AuditLogFactory>>
     */
    public function scopeForModel($query, string $modelType)
    {
        return $query->where('auditable_type', $modelType);
    }

    /**
     * Scope for date range.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<AuditLog<Database\Factories\AuditLogFactory>>  $query
     * @param  \Carbon\Carbon|string  $startDate
     * @param  \Carbon\Carbon|string  $endDate
     * @return \Illuminate\Database\Eloquent\Builder<AuditLog<Database\Factories\AuditLogFactory>>
     */
    public function scopeDateRange($query, $startDate, $endDate)
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
                $changes[] = "{$key}: {$oldValue} → {$newValue}";
            }
        }

        return implode(', ', $changes);
    }
}
