<?php

namespace Tests\Unit;

use App\Services\Procurement\PurchaseOrders\PurchaseOrderPrintLabels;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PurchaseOrderPrintLabelsTest extends TestCase
{
    #[Test]
    public function english_labels_use_default_form_text(): void
    {
        $labels = PurchaseOrderPrintLabels::resolve('en');

        $this->assertSame('Purchase Order', $labels->t('document_title'));
        $this->assertSame('Order information', $labels->t('order_information'));
        $this->assertFalse($labels->isRtl());
    }

    #[Test]
    public function arabic_labels_translate_static_form_text(): void
    {
        $labels = PurchaseOrderPrintLabels::resolve('ar');

        $this->assertSame('أمر شراء', $labels->t('document_title'));
        $this->assertSame('معلومات الطلب', $labels->t('order_information'));
        $this->assertTrue($labels->isRtl());
    }

    #[Test]
    public function arabic_labels_translate_enum_values(): void
    {
        $labels = PurchaseOrderPrintLabels::resolve('ar');

        $this->assertSame('شراء', $labels->procurementTypeLabel('purchase'));
        $this->assertSame('مورد', $labels->scopeTypeLabel('Supplier'));
        $this->assertSame('نعم', $labels->yesNo(true));
    }

    #[Test]
    public function vendor_row_labels_translate_for_print(): void
    {
        $labels = PurchaseOrderPrintLabels::resolve('ar');

        $this->assertSame('الاسم', $labels->vendorRowLabel('Name'));
        $this->assertSame('التصنيف', $labels->vendorRowLabel('Classification'));
    }

    #[Test]
    public function invalid_locale_falls_back_to_english(): void
    {
        $labels = PurchaseOrderPrintLabels::resolve('fr');

        $this->assertSame('en', $labels->locale());
        $this->assertSame('Subtotal', $labels->t('subtotal'));
    }
}
