<?php

namespace App\Services\Procurement\PurchaseOrders;

use App\Enums\Procurement\ProcurementRequests\GeographicScope;
use App\Enums\Procurement\ProcurementRequests\ProcurementType;
use App\Enums\Procurement\Rfqs\RfqTermsLocale;
use App\Models\Procurement\ProcurementRequests\ProcurementRequest;
use App\Support\Procurement\ProcurementCheckboxGroup;
use App\Support\Procurement\ProcurementScopeType;

class PurchaseOrderPrintLabels
{
    private const EN = [
        'document_title' => 'Purchase Order',
        'department' => "Procurement\nDepartment",
        'address' => 'Address:',
        'phone' => 'Phone:',
        'email' => 'Email:',
        'fax' => 'FAX:',
        'order_information' => 'Order information',
        'po_number' => 'P.O. number:',
        'date' => 'Date:',
        'pr_number' => 'P.R. number:',
        'procurement_type' => 'Procurement type:',
        'local_international' => 'Local / International:',
        'category' => 'Category:',
        'scope_type' => 'Scope Type:',
        'project' => 'Project:',
        'package' => 'Package:',
        'vendor_details' => 'Vendor Details',
        'vendor_name' => 'Name',
        'vendor_email' => 'Email',
        'vendor_phone' => 'Phone',
        'vendor_whatsapp' => 'WhatsApp',
        'vendor_position' => 'Position',
        'vendor_classification' => 'Classification',
        'delivery_details' => 'Delivery Details',
        'delivery_location' => 'Delivery location',
        'contact_person' => 'Contact person',
        'item' => 'Item',
        'description' => "Item or service\ndescription",
        'scope_of_work' => "Scope of\nwork",
        'quantity' => 'Quantity',
        'unit' => 'Unit',
        'price_per_unit' => "Price per\nunit",
        'line_total' => 'Line Total',
        'subtotal' => 'Subtotal',
        'delivery_fee' => 'Delivery fee',
        'discount' => 'Discount',
        'total_price' => 'Total Price:',
        'supporting_documents' => 'Supporting documents',
        'order_terms' => 'Order terms',
        'handover_date' => 'Handover date (maintenance from):',
        'dismantling_days' => 'Dismantling days (if any):',
        'dismantling_date' => 'Dismantling date (if any):',
        'days' => 'days',
        'payment_terms' => 'Payment terms:',
        'notes' => 'Notes:',
        'terms_and_conditions' => 'Terms and conditions :',
        'retention_by_year' => 'Retention by year',
        'retention_percent' => 'Retention %',
        'release_period' => 'Release period',
        'maintenance_internal' => 'Maintenance (internal)',
        'after_sale_service' => 'After-sale service:',
        'warranty_years' => 'Warranty & guarantee period (years):',
        'coverage_scope' => 'Coverage / scope:',
        'procurement' => 'Procurement:',
        'vendor' => 'Vendor:',
        'signature' => 'Signature:',
        'form_po' => 'Form PO',
        'print_preview' => 'print preview',
        'print' => 'Print',
        'back_to_po' => 'Back to PO',
        'language' => 'Language',
        'include_terms' => 'Terms & conditions',
        'with_terms' => 'With terms',
        'without_terms' => 'Without terms',
        'yes' => 'Yes',
        'no' => 'No',
        'em_dash' => '—',
    ];

    private const AR = [
        'document_title' => 'أمر شراء',
        'department' => 'قسم المشتريات',
        'address' => 'العنوان:',
        'phone' => 'الهاتف:',
        'email' => 'البريد الإلكتروني:',
        'fax' => 'الفاكس:',
        'order_information' => 'معلومات الطلب',
        'po_number' => 'رقم أمر الشراء:',
        'date' => 'التاريخ:',
        'pr_number' => 'رقم طلب الشراء:',
        'procurement_type' => 'نوع الشراء:',
        'local_international' => 'محلي / دولي:',
        'category' => 'الفئة:',
        'scope_type' => 'نوع النطاق:',
        'project' => 'المشروع:',
        'package' => 'الحزمة:',
        'vendor_details' => 'تفاصيل المورد',
        'vendor_name' => 'الاسم',
        'vendor_email' => 'البريد الإلكتروني',
        'vendor_phone' => 'الهاتف',
        'vendor_whatsapp' => 'واتساب',
        'vendor_position' => 'المنصب',
        'vendor_classification' => 'التصنيف',
        'delivery_details' => 'تفاصيل التسليم',
        'delivery_location' => 'موقع التسليم',
        'contact_person' => 'جهة الاتصال',
        'item' => 'البند',
        'description' => 'وصف البند أو الخدمة',
        'scope_of_work' => 'نطاق العمل',
        'quantity' => 'الكمية',
        'unit' => 'الوحدة',
        'price_per_unit' => 'سعر الوحدة',
        'line_total' => 'إجمالي البند',
        'subtotal' => 'المجموع الفرعي',
        'delivery_fee' => 'رسوم التسليم',
        'discount' => 'الخصم',
        'total_price' => 'السعر الإجمالي:',
        'supporting_documents' => 'المستندات الداعمة',
        'order_terms' => 'شروط الطلب',
        'handover_date' => 'تاريخ التسليم (بداية الصيانة):',
        'dismantling_days' => 'أيام الفك (إن وجد):',
        'dismantling_date' => 'تاريخ الفك (إن وجد):',
        'days' => 'يوم',
        'payment_terms' => 'شروط الدفع:',
        'notes' => 'ملاحظات:',
        'terms_and_conditions' => 'الشروط والأحكام:',
        'retention_by_year' => 'الاحتجاز حسب السنة',
        'retention_percent' => 'نسبة الاحتجاز %',
        'release_period' => 'فترة الإفراج',
        'maintenance_internal' => 'الصيانة (داخلي)',
        'after_sale_service' => 'خدمة ما بعد البيع:',
        'warranty_years' => 'فترة الضمان والكفالة (سنوات):',
        'coverage_scope' => 'نطاق التغطية:',
        'procurement' => 'المشتريات:',
        'vendor' => 'المورد:',
        'signature' => 'التوقيع:',
        'form_po' => 'نموذج أمر شراء',
        'print_preview' => 'معاينة الطباعة',
        'print' => 'طباعة',
        'back_to_po' => 'العودة لأمر الشراء',
        'language' => 'اللغة',
        'include_terms' => 'الشروط والأحكام',
        'with_terms' => 'مع الشروط',
        'without_terms' => 'بدون الشروط',
        'yes' => 'نعم',
        'no' => 'لا',
        'em_dash' => '—',
    ];

    /** @var array<string, string> */
    private const VENDOR_ROW_KEYS = [
        'Name' => 'vendor_name',
        'Email' => 'vendor_email',
        'Phone' => 'vendor_phone',
        'WhatsApp' => 'vendor_whatsapp',
        'Position' => 'vendor_position',
        'Classification' => 'vendor_classification',
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

    public function vendorRowLabel(string $englishLabel): string
    {
        $key = self::VENDOR_ROW_KEYS[$englishLabel] ?? null;

        return $key !== null ? $this->t($key) : $englishLabel;
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

    public function procurementTypeDisplayFromRequest(ProcurementRequest $request): string
    {
        return ProcurementCheckboxGroup::display(
            $request->procurement_types,
            ProcurementType::values(),
            fn (string $value) => $this->procurementTypeLabel($value)
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

    public function geographicScopeDisplayFromRequest(ProcurementRequest $request): string
    {
        $selected = GeographicScope::selectedValues($request->geographic_scopes);
        if ($selected === []) {
            return '';
        }

        if (count(array_intersect(GeographicScope::values(), $selected)) === count(GeographicScope::values())) {
            return $this->locale === RfqTermsLocale::Ar->value ? 'محلي ودولي' : 'Both';
        }

        return implode(', ', array_map(
            fn (string $value) => $this->geographicScopeLabel($value),
            $selected
        ));
    }

    public function scopeTypeLabel(string $value): string
    {
        if ($this->locale !== RfqTermsLocale::Ar->value) {
            return ProcurementScopeType::label($value);
        }

        return match ($value) {
            ProcurementScopeType::Supplier => 'مورد',
            ProcurementScopeType::Contractor => 'مقاول',
            ProcurementScopeType::Studies => 'دراسات',
            default => ProcurementScopeType::label($value),
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
}
