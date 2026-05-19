<?php

namespace App\Services\Activity;

use App\Models\Activity\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    /**
     * @param  array<string, mixed>|null  $changes
     */
    public function log(
        string $action,
        ?string $model = null,
        ?int $modelId = null,
        ?array $changes = null,
        ?string $description = null,
        ?int $userId = null,
        ?Request $request = null,
    ): ActivityLog {
        $request ??= request();

        return ActivityLog::query()->create([
            'user_id' => $userId ?? Auth::id(),
            'action' => $action,
            'model' => $model,
            'model_id' => $modelId,
            'changes' => $changes,
            'description' => $description,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'created_at' => now(),
        ]);
    }

    public function logLogin(?int $userId = null, ?Request $request = null): ActivityLog
    {
        $userId ??= Auth::id();

        return $this->log(
            action: 'login',
            description: 'User logged in',
            userId: $userId,
            request: $request,
        );
    }

    public function logLogout(?int $userId = null, ?Request $request = null): ActivityLog
    {
        return $this->log(
            action: 'logout',
            description: 'User logged out',
            userId: $userId,
            request: $request,
        );
    }

    public function logModelEvent(
        Model $model,
        string $event,
        ?array $changes = null,
        ?string $description = null,
        ?int $userId = null,
        ?Request $request = null,
    ): ActivityLog {
        $key = method_exists($model, 'activityLogKey')
            ? $model->activityLogKey()
            : class_basename($model);

        return $this->log(
            action: "{$event}_{$key}",
            model: $model::class,
            modelId: (int) $model->getKey(),
            changes: $changes,
            description: $description ?? $model->activityLogDescription($event),
            userId: $userId ?? $this->resolveUserIdForModel($model),
            request: $request,
        );
    }

    protected function resolveUserIdForModel(Model $model): ?int
    {
        if (Auth::id()) {
            return (int) Auth::id();
        }

        if (isset($model->created_by)) {
            return (int) $model->created_by;
        }

        return null;
    }
}
