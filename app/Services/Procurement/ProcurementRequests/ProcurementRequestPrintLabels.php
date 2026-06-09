<?php

namespace App\Services\Procurement\ProcurementRequests;

use App\Enums\Procurement\ProcurementRequests\GeographicScope;
use App\Enums\Procurement\ProcurementRequests\ProcurementTimelineActivity;
use App\Enums\Procurement\ProcurementRequests\ProcurementType;
use App\Enums\Procurement\ProcurementRequests\ProcurementVendorType;
use App\Enums\Procurement\Rfqs\RfqTermsLocale;
use App\Support\Procurement\ProcurementCheckboxGroup;

class ProcurementRequestPrintLabels
{
    private const EN = [
        'document_title' => 'Procurement Request',
        'department' => 'Procurement Department',
        'request_information' => 'Request information',
        'pr_number' => 'P.R. number:',
        'date' => 'Date:',
        'classification' => 'Classification:',
        'requestor' => 'Requestor:',
        'department_label' => 'Department:',
        'received_by' => 'Received by:',
        'pr_information' => 'PR information',
        'company' => 'Company:',
        'project' => 'Project:',
        'zone' => 'Zone:',
        'category' => 'Category:',
        'subcategory' => 'Subcategory:',
        'procurement_type' => 'Procurement type:',
        'local_international' => 'Local / International:',
        'local_international_hint' => 'Select both for Local & International.',
        'vendor_type' => 'Vendor type:',
        'procurement_note' => 'Procurement note',
        'boq' => 'BOQ',
        'item' => 'Item',
        'description' => 'Description',
        'qty' => 'Qty',
        'unit' => 'Unit',
        'unit_price' => 'Unit price',
        'total' => 'Total',
        'no_line_items' => 'No line items.',
        'samples_required' => 'Samples required:',
        'justification' => 'Justification:',
        'lead_time_days' => 'Lead time (days):',
        'delivery_location' => 'Delivery location:',
        'scope_of_work' => 'Scope of work:',
        'supporting_documents' => 'Supporting documents',
        'procurement_timeline' => 'Procurement timeline',
        'timeline_activity' => 'Activity',
        'timeline_duration_days' => 'Duration (days)',
        'timeline_final_delivery_date' => 'Final delivery date',
        'timeline_days' => 'days',
        'timeline_final_delivery_note' => 'Same as required delivery lead time (from PO issuance).',
        'form_pr' => 'Form PR',
        'print_preview' => 'print preview',
        'print' => 'Print',
        'back_to_pr' => 'Back to PR',
        'language' => 'Language',
        'yes' => 'Yes',
        'no' => 'No',
        'em_dash' => '—',
    ];

    private const AR = [
        'document_title' => 'طلب شراء',
        'department' => 'قسم المشتريات',
        'request_information' => 'معلومات الطلب',
        'pr_number' => 'رقم طلب الشراء:',
        'date' => 'التاريخ:',
        'classification' => 'التصنيف:',
        'requestor' => 'مقدم الطلب:',
        'department_label' => 'القسم:',
        'received_by' => 'استلم بواسطة:',
        'pr_information' => 'معلومات طلب الشراء',
        'company' => 'الشركة:',
        'project' => 'المشروع:',
        'zone' => 'المنطقة:',
        'category' => 'الفئة:',
        'subcategory' => 'الفئة الفرعية:',
        'procurement_type' => 'نوع الشراء:',
        'local_international' => 'محلي / دولي:',
        'local_international_hint' => 'اختر الاثنين للمحلي والدولي.',
        'vendor_type' => 'نوع المورد:',
        'procurement_note' => 'ملاحظة المشتريات',
        'boq' => 'جدول الكميات',
        'item' => 'البند',
        'description' => 'الوصف',
        'qty' => 'الكمية',
        'unit' => 'الوحدة',
        'unit_price' => 'سعر الوحدة',
        'total' => 'المجموع',
        'no_line_items' => 'لا توجد بنود.',
        'samples_required' => 'عينات مطلوبة:',
        'justification' => 'المبرر:',
        'lead_time_days' => 'مدة التسليم (أيام):',
        'delivery_location' => 'موقع التسليم:',
        'scope_of_work' => 'نطاق العمل:',
        'supporting_documents' => 'المستندات الداعمة',
        'procurement_timeline' => 'الجدول الزمني للمشتريات',
        'timeline_activity' => 'النشاط',
        'timeline_duration_days' => 'المدة (أيام)',
        'timeline_final_delivery_date' => 'تاريخ التسليم النهائي',
        'timeline_days' => 'أيام',
        'timeline_final_delivery_note' => 'نفس مدة التسليم المطلوبة (من إصدار أمر الشراء).',
        'form_pr' => 'نموذج طلب شراء',
        'print_preview' => 'معاينة الطباعة',
        'print' => 'طباعة',
        'back_to_pr' => 'العودة لطلب الشراء',
        'language' => 'اللغة',
        'yes' => 'نعم',
        'no' => 'لا',
        'em_dash' => '—',
    ];

    private string $locale;

    public function __construct(?string $locale = null)
    {
        $this->locale = in_array($locale, RfqTermsLocale::values(), true)
            ? $locale
            : RfqTermsLocale::default()->value;
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

    public function yesNo(?bool $value): string
    {
        if ($value === null) {
            return $this->t('em_dash');
        }

        return $value ? $this->t('yes') : $this->t('no');
    }

    /**
     * @return list<array{value: string, label: string, checked: bool}>
     */
    public function procurementTypeCheckboxOptions(mixed $stored): array
    {
        return $this->checkboxOptions(
            $stored,
            ProcurementType::values(),
            fn (string $value) => $this->procurementTypeLabel($value)
        );
    }

    public function procurementTypeLabel(string $value): string
    {
        if ($this->locale !== RfqTermsLocale::Ar->value) {
            return ProcurementType::from($value)->label();
        }

        return match ($value) {
            ProcurementType::Purchase->value => 'شراء',
            ProcurementType::Rental->value => 'إيجار',
            default => ProcurementType::from($value)->label(),
        };
    }

    public function geographicScopeBothSelected(mixed $stored): bool
    {
        $selected = GeographicScope::selectedValues($stored);

        return count(array_intersect(GeographicScope::values(), $selected)) === count(GeographicScope::values());
    }

    /**
     * @return list<array{value: string, label: string, checked: bool}>
     */
    public function geographicScopeCheckboxOptions(mixed $stored): array
    {
        return $this->checkboxOptions(
            $stored,
            GeographicScope::values(),
            fn (string $value) => $this->geographicScopeLabel($value)
        );
    }

    public function geographicScopeLabel(string $value): string
    {
        if ($this->locale !== RfqTermsLocale::Ar->value) {
            return GeographicScope::from($value)->label();
        }

        return match ($value) {
            GeographicScope::Local->value => 'محلي',
            GeographicScope::International->value => 'دولي',
            default => GeographicScope::from($value)->label(),
        };
    }

    /**
     * @return list<array{value: string, label: string, checked: bool}>
     */
    public function vendorTypeCheckboxOptions(mixed $stored): array
    {
        return $this->checkboxOptions(
            $stored,
            ProcurementVendorType::values(),
            fn (string $value) => $this->vendorTypeLabel($value)
        );
    }

    public function vendorTypeLabel(string $value): string
    {
        if ($this->locale !== RfqTermsLocale::Ar->value) {
            return ProcurementVendorType::from($value)->label();
        }

        return match ($value) {
            ProcurementVendorType::Contractor->value => 'مقاول',
            ProcurementVendorType::Supplier->value => 'مورد',
            ProcurementVendorType::Studies->value => 'دراسات',
            default => ProcurementVendorType::from($value)->label(),
        };
    }

    public function categoryName(?object $category, ?string $legacy = null): string
    {
        if ($category === null) {
            return filled($legacy) ? $legacy : $this->t('em_dash');
        }

        if ($this->locale === RfqTermsLocale::Ar->value && filled($category->name_ar ?? null)) {
            return $category->name_ar;
        }

        return $category->name_en ?? $legacy ?? $this->t('em_dash');
    }

    public function subcategoryName(?object $subcategory, ?string $legacy = null): string
    {
        if ($subcategory === null) {
            return filled($legacy) ? $legacy : $this->t('em_dash');
        }

        if ($this->locale === RfqTermsLocale::Ar->value && filled($subcategory->name_ar ?? null)) {
            return $subcategory->name_ar;
        }

        return $subcategory->name_en ?? $legacy ?? $this->t('em_dash');
    }

    /**
     * @param  list<string>  $allowed
     * @return list<array{value: string, label: string, checked: bool}>
     */
    private function checkboxOptions(mixed $stored, array $allowed, callable $labelFn): array
    {
        $selected = ProcurementCheckboxGroup::selectedValues($stored, $allowed);

        return array_map(
            fn (string $value) => [
                'value' => $value,
                'label' => $labelFn($value),
                'checked' => in_array($value, $selected, true),
            ],
            $allowed
        );
    }

    public function timelineActivityLabel(string $activity): string
    {
        if ($this->locale !== RfqTermsLocale::Ar->value) {
            return ProcurementTimelineActivity::from($activity)->label();
        }

        return match ($activity) {
            ProcurementTimelineActivity::RfqIssuance->value => 'إصدار طلب عروض الأسعار',
            ProcurementTimelineActivity::QuotationSubmission->value => 'تقديم العروض',
            ProcurementTimelineActivity::TechnicalEvaluation->value => 'التقييم الفني',
            ProcurementTimelineActivity::CommercialEvaluation->value => 'التقييم التجاري',
            ProcurementTimelineActivity::Negotiation->value => 'التفاوض',
            ProcurementTimelineActivity::ApprovalProcess->value => 'عملية الموافقة',
            ProcurementTimelineActivity::ContractAward->value => 'ترسية العقد',
            ProcurementTimelineActivity::PoIssuance->value => 'إصدار أمر الشراء',
            default => ProcurementTimelineActivity::from($activity)->label(),
        };
    }
}
