<?php

namespace App\Http\Requests\Procurement\ScheduleOfWorks;

class UpdateScheduleOfWorkRequest extends StoreScheduleOfWorkRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('schedule-of-works.create') ?? false;
    }
}
