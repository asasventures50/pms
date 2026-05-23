<?php

namespace App\Support\Procurement;

use App\Services\Procurement\Rfqs\RfqGeneralTermsService;

final class RfqTerms
{
    /**
     * Active general terms from the library, or legacy defaults when the table is empty.
     *
     * @return list<string>
     */
    public static function defaults(): array
    {
        return app(RfqGeneralTermsService::class)->activeTexts();
    }

    /**
     * Built-in defaults used for seeding and RFQs created before terms were stored.
     *
     * @return list<string>
     */
    public static function legacyDefaults(): array
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
