<?php

namespace Tests\Unit;

use App\Models\Procurement\PurchaseOrders\PurchaseOrder;
use App\Models\Procurement\Rfqs\RfqGeneralTerm;
use App\Services\Procurement\Rfqs\RfqGeneralTermsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseOrderStoredTermsTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchase_order_show_and_print_use_stored_general_terms_not_live_library(): void
    {
        RfqGeneralTerm::query()->create([
            'body_en' => 'New library term',
            'body_ar' => 'New library term AR',
            'sort_order' => 0,
            'is_active' => true,
            'scope_types' => null,
        ]);

        $purchaseOrder = new PurchaseOrder([
            'terms' => [
                'general' => ['Old signed term'],
                'custom' => [],
            ],
            'terms_locale' => 'en',
        ]);

        $service = new RfqGeneralTermsService;

        $stored = $service->resolveStoredTermsForLocale(
            $purchaseOrder->terms,
            $purchaseOrder->terms_locale,
        );
        $live = $service->resolveLiveTermsForPurchaseOrder($purchaseOrder);

        $this->assertSame(['Old signed term'], $stored);
        $this->assertContains('New library term', $live);
        $this->assertNotContains('Old signed term', $live);
    }
}
