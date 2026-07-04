<?php

namespace App\Enums\Procurement\Flow;

enum FlowStageState: string
{
    case Completed = 'completed';
    case Active = 'active';
    case Pending = 'pending';
    case Cancelled = 'cancelled';
}
