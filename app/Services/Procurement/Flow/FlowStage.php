<?php

namespace App\Services\Procurement\Flow;

use App\Enums\Procurement\Flow\FlowStageKey;
use App\Enums\Procurement\Flow\FlowStageState;

final class FlowStage
{
    public function __construct(
        public FlowStageKey $key,
        public FlowStageState $state,
        public string $label,
        public ?int $badge = null,
        public ?string $badgeLabel = null,
        public ?string $detail = null,
    ) {}
}
