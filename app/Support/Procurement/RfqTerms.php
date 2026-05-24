<?php

namespace App\Support\Procurement;

use App\Enums\Procurement\Rfqs\RfqTermsLocale;
use App\Services\Procurement\Rfqs\RfqGeneralTermsService;

final class RfqTerms
{
    /**
     * Active general terms from the library, or legacy defaults when the table is empty.
     *
     * @return list<string>
     */
    public static function defaults(?string $locale = null): array
    {
        return app(RfqGeneralTermsService::class)->activeTexts($locale);
    }

    /**
     * Built-in defaults used for seeding and RFQs created before terms were stored.
     *
     * @return list<string>
     */
    public static function legacyDefaults(?string $locale = null): array
    {
        $locale = self::normalizeLocale($locale);

        return $locale === RfqTermsLocale::Ar->value
            ? self::legacyDefaultsAr()
            : self::legacyDefaultsEn();
    }

    /**
     * @return list<string>
     */
    public static function legacyDefaultsEn(): array
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

    /**
     * @return list<string>
     */
    public static function legacyDefaultsAr(): array
    {
        return [
            'يجب أن تشمل الأسعار جميع الرسوم والتكاليف المطبقة.',
            'يجب ذكر أي انحرافات عن المواصفات بشكل واضح.',
            'تحتفظ الشركة بالحق في رفض العروض غير المكتملة.',
            'تحتفظ الشركة بالحق في خصم 5% من قيمة الطلب الإجمالية من الدفعة النهائية عن كل يوم تأخير.',
            'يجب الإشارة إلى رقم طلب عرض السعر في جميع المراسلات.',
            'تحتفظ الشركة بالحق في رفض وإرجاع أي بضائع لا تستوفي معايير الجودة المحددة على نفقة المورد.',
        ];
    }

    private static function normalizeLocale(?string $locale): string
    {
        $locale = $locale ?? RfqTermsLocale::default()->value;

        return in_array($locale, RfqTermsLocale::values(), true)
            ? $locale
            : RfqTermsLocale::default()->value;
    }
}
