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
        );
    }

    public function updated(Model $model): void
    {
        if (! $this->shouldLog($model)) {
            return;
        }

        $changed = array_keys($model->getChanges());
        $excluded = method_exists($model, 'activityLogExcludedAttributes')
            ? $model->activityLogExcludedAttributes()
            : [];
        $changed = array_values(array_diff($changed, $excluded));

        if ($changed === []) {
            return;
        }

        $this->logger->logModelEvent(
            model: $model,
            event: 'update',
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
        );
    }

    protected function shouldLog(Model $model): bool
    {
        return in_array(LogsActivity::class, class_uses_recursive($model), true);
    }
}
