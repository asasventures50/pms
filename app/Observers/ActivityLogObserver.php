<?php

namespace App\Observers;

use App\Models\Concerns\LogsActivity;
use App\Services\Activity\ActivityLogger;
use Illuminate\Database\Eloquent\Model;

class ActivityLogObserver
{
    public function __construct(
        protected ActivityLogger $logger,
    ) {}

    public function created(Model $model): void
    {
        if (! $this->shouldLog($model)) {
            return;
        }

        $this->logger->logModelEvent(
            model: $model,
            event: 'create',
            changes: [
                'new' => $model->activityLogSnapshot(),
            ],
        );
    }

    public function updated(Model $model): void
    {
        if (! $this->shouldLog($model)) {
            return;
        }

        $changed = array_keys($model->getChanges());
        $excluded = $model->activityLogExcludedAttributes();
        $changed = array_values(array_diff($changed, $excluded));

        if ($changed === []) {
            return;
        }

        $old = [];
        $new = [];

        foreach ($changed as $attribute) {
            $old[$attribute] = $model->normalizeActivityLogValue($model->getOriginal($attribute));
            $new[$attribute] = $model->normalizeActivityLogValue($model->getAttribute($attribute));
        }

        $this->logger->logModelEvent(
            model: $model,
            event: 'update',
            changes: [
                'old' => $old,
                'new' => $new,
            ],
        );
    }

    public function deleted(Model $model): void
    {
        if (! $this->shouldLog($model)) {
            return;
        }

        $this->logger->logModelEvent(
            model: $model,
            event: 'delete',
            changes: [
                'old' => $model->activityLogSnapshot(),
            ],
        );
    }

    public function restored(Model $model): void
    {
        if (! $this->shouldLog($model)) {
            return;
        }

        $this->logger->logModelEvent(
            model: $model,
            event: 'restore',
            changes: [
                'new' => $model->activityLogSnapshot(),
            ],
        );
    }

    protected function shouldLog(Model $model): bool
    {
        return in_array(LogsActivity::class, class_uses_recursive($model), true);
    }
}
