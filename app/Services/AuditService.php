<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditService
{
    /**
     * Log an audit event.
     */
    public function log(
        string $event,
        Model $model,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?array $metadata = null,
        ?Request $request = null
    ): void {
        $user = Auth::user();
        $request = $request ?? request();

        AuditLog::create([
            'event' => $event,
            'auditable_type' => get_class($model),
            'auditable_id' => $model->getKey(),
            'user_id' => $user?->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'metadata' => $metadata,
            'url' => $request->fullUrl(),
            'method' => $request->method(),
        ]);
    }

    /**
     * Log model creation.
     */
    public function logCreated(Model $model, ?array $metadata = null): void
    {
        $this->log('created', $model, null, $model->getAttributes(), $metadata);
    }

    /**
     * Log model update.
     */
    public function logUpdated(Model $model, array $oldValues, ?array $metadata = null): void
    {
        $this->log('updated', $model, $oldValues, $model->getChanges(), $metadata);
    }

    /**
     * Log model deletion.
     */
    public function logDeleted(Model $model, ?array $metadata = null): void
    {
        $this->log('deleted', $model, $model->getAttributes(), null, $metadata);
    }

    /**
     * Log model viewing.
     */
    public function logViewed(Model $model, ?array $metadata = null): void
    {
        $this->log('viewed', $model, null, null, $metadata);
    }

    /**
     * Log sensitive operations.
     */
    public function logSensitiveOperation(
        string $operation,
        Model $model,
        ?array $metadata = null
    ): void {
        $this->log($operation, $model, null, null, $metadata);
    }

    /**
     * Log authentication events.
     */
    public function logAuthEvent(string $event, ?int $userId = null, ?array $metadata = null): void
    {
        $user = $userId ? \App\Models\User::find($userId) : Auth::user();

        if ($user) {
            $this->log($event, $user, null, null, $metadata);
        }
    }

    /**
     * Log API access.
     */
    public function logApiAccess(
        string $endpoint,
        string $method,
        ?int $userId = null,
        ?array $metadata = null
    ): void {
        $user = $userId ? \App\Models\User::find($userId) : Auth::user();

        if ($user) {
            $this->log('api_access', $user, null, null, array_merge($metadata ?? [], [
                'endpoint' => $endpoint,
                'method' => $method,
            ]));
        }
    }

    /**
     * Get audit logs for a model.
     */
    public function getModelLogs(Model $model, ?int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return AuditLog::where('auditable_type', get_class($model))
            ->where('auditable_id', $model->getKey())
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get audit logs for a user.
     */
    public function getUserLogs(int $userId, ?int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return AuditLog::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get audit logs by event.
     */
    public function getEventLogs(string $event, ?int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return AuditLog::where('event', $event)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Clean old audit logs.
     */
    public function cleanOldLogs(int $daysOld = 90): int
    {
        return AuditLog::where('created_at', '<', now()->subDays($daysOld))->delete();
    }
}
