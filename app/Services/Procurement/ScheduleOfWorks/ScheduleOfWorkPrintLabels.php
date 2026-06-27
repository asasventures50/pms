<?php

namespace App\Services\Procurement\ScheduleOfWorks;

use App\Enums\Procurement\Rfqs\RfqTermsLocale;
use App\Enums\Procurement\ScheduleOfWorks\ScheduleOfWorkScope;

class ScheduleOfWorkPrintLabels
{
    private const EN = [
        'document_title' => 'Schedule of Works',
        'document_number' => 'Document #',
        'date' => 'Date',
        'recipient_label' => 'To:',
        'scope_of_work' => 'Scope of work',
        'po_reference' => 'P.O. reference',
        'vendor' => 'Vendor',
        'col_num' => '#',
        'col_project' => 'Project / Zone',
        'col_desc' => 'Description',
        'col_qty' => 'Qty',
        'col_unit' => 'Unit',
        'col_unit_price' => 'Unit price',
        'col_total' => 'Total',
        'grand_total' => 'Grand total',
        'notes' => 'Notes:',
        'terms_and_conditions' => 'Terms and conditions:',
        'bank_title' => 'Bank information',
        'signature_receive' => 'Client receipt',
        'signature_accounts' => 'Accounts department',
        'signature_general' => 'General management',
        'print' => 'Print',
        'back' => 'Back to schedules',
        'print_preview' => 'Print preview',
        'language' => 'Language',
        'em_dash' => '—',
    ];

    private const AR = [
        'document_title' => 'كشف أعمال',
        'document_number' => 'رقم الكشف',
        'date' => 'التاريخ',
        'recipient_label' => 'السيد / السادة:',
        'scope_of_work' => 'نطاق العمل',
        'po_reference' => 'مرجع أمر الشراء',
        'vendor' => 'المورد',
        'col_num' => 'م',
        'col_project' => 'المشروع / المنطقة',
        'col_desc' => 'البيان',
        'col_qty' => 'الكمية',
        'col_unit' => 'الوحدة',
        'col_unit_price' => 'سعر الوحدة',
        'col_total' => 'المجموع',
        'grand_total' => 'المجموع الكلي',
        'notes' => 'ملاحظات:',
        'terms_and_conditions' => 'الشروط والأحكام:',
        'bank_title' => 'معلومات البنك',
        'signature_receive' => 'استلام العميل',
        'signature_accounts' => 'إدارة الحسابات',
        'signature_general' => 'الإدارة العامة',
        'print' => 'طباعة',
        'back' => 'العودة لكشوف الأعمال',
        'print_preview' => 'معاينة الطباعة',
        'language' => 'اللغة',
        'em_dash' => '—',
    ];

    private string $locale;

    public function __construct(?string $locale = null)
    {
        $this->locale = in_array($locale, RfqTermsLocale::values(), true)
            ? $locale
            : RfqTermsLocale::Ar->value;
    }

    public static function resolve(?string $locale): self
    {
        return new self($locale);
    }

    public function locale(): string
    {
        return $this->locale;
    }

    public function isRtl(): bool
    {
        return $this->locale === RfqTermsLocale::Ar->value;
    }

    public function t(string $key): string
    {
        $labels = $this->locale === RfqTermsLocale::Ar->value ? self::AR : self::EN;

        return $labels[$key] ?? self::EN[$key] ?? $key;
    }

    public function scopeLabel(string $value): string
    {
        return ScheduleOfWorkScope::labelFor($value, $this->isRtl());
    }
}
