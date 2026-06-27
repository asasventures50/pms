<?php

namespace App\Services\Procurement\ScheduleOfWorks;

use App\Enums\Procurement\ScheduleOfWorks\ScheduleOfWorkScope;
use App\Models\Procurement\ScheduleOfWorks\ScheduleOfWork;
use App\Services\Procurement\Rfqs\RfqGeneralTermsService;
use App\Support\Procurement\ProcurementScopeType;

class ScheduleOfWorkTermsResolver
{
    public function __construct(
        private readonly RfqGeneralTermsService $termsService,
    ) {}

    /**
     * General + scope-specific terms from the RFQ terms library (same source as PO print).
     *
     * @return list<string>
     */
    public function resolve(ScheduleOfWork $schedule, ?string $locale = null): array
    {
        $locale = $locale ?? $schedule->print_locale;
        $rfqScopeTypes = $this->rfqScopeTypesFromSchedule($schedule);

        return $this->termsService->activeTextsForScopeTypes($rfqScopeTypes, $locale);
    }

    /**
     * @return list<string>
     */
    public function rfqScopeTypesFromSchedule(ScheduleOfWork $schedule): array
    {
        $selected = ScheduleOfWorkScope::selectedValues($schedule->scope_types ?? []);
        $mapped = [];

        foreach ($selected as $scopeValue) {
            $scope = ScheduleOfWorkScope::tryFrom($scopeValue);
            if ($scope === null || $scope === ScheduleOfWorkScope::Global) {
                continue;
            }

            foreach ($scope->rfqTermsScopeTypes() as $rfqScopeType) {
                $mapped[$rfqScopeType] = true;
            }
        }

        $ordered = [];
        foreach (ProcurementScopeType::options() as $option) {
            if (isset($mapped[$option])) {
                $ordered[] = $option;
            }
        }

        return $ordered;
    }
}
