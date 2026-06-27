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
        'scope_types' => 'Scope types',
        'pr_information' => 'PR information',
        'project' => 'Project',
        'zone' => 'Zone',
        'category' => 'Category',
        'subcategory' => 'Subcategory',
        'procurement_type' => 'Procurement type',
        'geographic_scope' => 'Local / International',
        'vendor_type' => 'Vendor type',
        'samples_required' => 'Samples required',
        'delivery_requirements' => 'Delivery requirements',
        'lead_time_days' => 'Lead time (days)',
        'delivery_location' => 'Delivery location',
        'flexible_delivery' => 'Flexible delivery date',
        'supporting_documents' => 'Supporting documents',
        'payment_terms' => 'Payment terms',
        'milestone' => 'Milestone',
        'note' => 'Note',
        'due_upon' => 'Due upon',
        'retention' => 'Retention',
        'retention_percent' => 'Retention %',
        'release_period' => 'Release period',
        'maintenance' => 'Maintenance',
        'after_sale_service' => 'After-sale service',
        'warranty_years' => 'Warranty (years)',
        'warranty_coverage' => 'Warranty coverage',
        'timeline' => 'Procurement timeline',
        'activity' => 'Activity',
        'duration_days' => 'Duration (days)',
        'compliance' => 'Compliance requirements',
        'verification_required' => 'Required verification',
        'prequalification_required' => 'Required prequalification',
        'prequalification_level' => 'Prequalification level',
        'nda_required' => 'NDA required',
        'conflict_of_interest' => 'Conflict of interest',
        'commitment_compliance' => 'Declaration of commitment and compliance',
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
        'scope_types' => 'أنواع النطاق',
        'pr_information' => 'معلومات طلب الشراء',
        'project' => 'المشروع',
        'zone' => 'المنطقة',
        'category' => 'الفئة',
        'subcategory' => 'الفئة الفرعية',
        'procurement_type' => 'نوع الشراء',
        'geographic_scope' => 'محلي / دولي',
        'vendor_type' => 'نوع المورد',
        'samples_required' => 'عينات مطلوبة',
        'delivery_requirements' => 'متطلبات التسليم',
        'lead_time_days' => 'مدة التسليم (أيام)',
        'delivery_location' => 'موقع التسليم',
        'flexible_delivery' => 'تاريخ تسليم مرن',
        'supporting_documents' => 'المستندات الداعمة',
        'payment_terms' => 'شروط الدفع',
        'milestone' => 'المرحلة',
        'note' => 'ملاحظة',
        'due_upon' => 'مستحق عند',
        'retention' => 'الاحتجاز',
        'retention_percent' => 'نسبة الاحتجاز',
        'release_period' => 'فترة الإفراج',
        'maintenance' => 'الصيانة',
        'after_sale_service' => 'خدمة ما بعد البيع',
        'warranty_years' => 'الضمان (سنوات)',
        'warranty_coverage' => 'نطاق الضمان',
        'timeline' => 'الجدول الزمني للمشتريات',
        'activity' => 'النشاط',
        'duration_days' => 'المدة (أيام)',
        'compliance' => 'متطلبات الامتثال',
        'verification_required' => 'التحقق مطلوب',
        'prequalification_required' => 'التأهيل المسبق مطلوب',
        'prequalification_level' => 'مستوى التأهيل المسبق',
        'nda_required' => 'اتفاقية سرية مطلوبة',
        'conflict_of_interest' => 'تضارب المصالح',
        'commitment_compliance' => 'إقرار الالتزام والامتثال',
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
