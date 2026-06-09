<?php

namespace Tests\Unit;

use App\Services\Procurement\ProcurementRequests\ProcurementRequestPrintLabels;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProcurementRequestPrintLabelsTest extends TestCase
{
    #[Test]
    public function english_labels_use_default_form_text(): void
    {
        $labels = ProcurementRequestPrintLabels::resolve('en');

        $this->assertSame('Procurement Request', $labels->t('document_title'));
        $this->assertSame('Request information', $labels->t('request_information'));
        $this->assertFalse($labels->isRtl());
    }

    #[Test]
    public function arabic_labels_translate_static_form_text(): void
    {
        $labels = ProcurementRequestPrintLabels::resolve('ar');

        $this->assertSame('طلب شراء', $labels->t('document_title'));
        $this->assertSame('معلومات الطلب', $labels->t('request_information'));
        $this->assertTrue($labels->isRtl());
    }

    #[Test]
    public function arabic_labels_translate_enum_values(): void
    {
        $labels = ProcurementRequestPrintLabels::resolve('ar');

        $this->assertSame('شراء', $labels->procurementTypeLabel('purchase'));
        $this->assertSame('مورد', $labels->vendorTypeLabel('supplier'));
        $this->assertSame('نعم', $labels->yesNo(true));
    }

    #[Test]
    public function procurement_type_print_shows_individual_checkbox_options(): void
    {
        $labels = ProcurementRequestPrintLabels::resolve('en');
        $options = $labels->procurementTypeCheckboxOptions(['purchase', 'rental']);

        $this->assertSame([
            ['value' => 'purchase', 'label' => 'Purchase', 'checked' => true],
            ['value' => 'rental', 'label' => 'Rental', 'checked' => true],
        ], $options);
    }

    #[Test]
    public function geographic_scope_print_shows_individual_checkbox_options(): void
    {
        $labels = ProcurementRequestPrintLabels::resolve('en');
        $options = $labels->geographicScopeCheckboxOptions(['local', 'international']);

        $this->assertTrue($labels->geographicScopeBothSelected(['local', 'international']));
        $this->assertSame([
            ['value' => 'local', 'label' => 'Local', 'checked' => true],
            ['value' => 'international', 'label' => 'International', 'checked' => true],
        ], $options);
    }

    #[Test]
    public function geographic_scope_print_uses_local_international_label(): void
    {
        $labels = ProcurementRequestPrintLabels::resolve('en');

        $this->assertSame('Local / International:', $labels->t('local_international'));
        $this->assertSame('Select both for Local & International.', $labels->t('local_international_hint'));
    }

    #[Test]
    public function invalid_locale_falls_back_to_english(): void
    {
        $labels = ProcurementRequestPrintLabels::resolve('fr');

        $this->assertSame('en', $labels->locale());
        $this->assertSame('BOQ', $labels->t('boq'));
    }
}
