<?php

namespace App\Support\Procurement;

final class RfqTerms
{
    /**
     * @return list<string>
     */
    public static function defaults(): array
    {
        return [
            'Prices must include all applicable charges.',
            'Any deviations must be clearly stated.',
            'Company reserves the right to reject incomplete quotations.',
            'The Company reserves the right to deduct 5% of the total order value from the final payment for each day of delay.',
            'RFQ number must be referenced in all communications.',
            'The Company reserves the right to reject and return any goods that do not meet specified quality standards at the Supplier\'s expense.',
        ];
    }
}
