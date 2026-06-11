<?php

namespace Tests\Unit;

use App\Models\Procurement\ProcurementRequests\ProcurementRequest;
use App\Models\Procurement\ProcurementRequests\ProcurementRequestPaymentTerm;
use App\Models\Procurement\ProcurementRequests\ProcurementRequestRetention;
use App\Services\Procurement\PurchaseOrders\ProcurementRequestCommercialTermsForPurchaseOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcurementRequestCommercialTermsForPurchaseOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_snapshot_formats_payment_terms_retention_and_maintenance_from_pr(): void
    {
        $request = ProcurementRequest::query()->create([
            'request_number' => 'PR-TEST-001',
            'after_sale_service_applicable' => true,
            'warranty_years' => 2,
            'warranty_coverage' => 'Parts and labor',
        ]);

        ProcurementRequestPaymentTerm::query()->create([
            'procurement_request_id' => $request->id,
            'sort_order' => 0,
            'milestone' => 'Advance',
            'amount' => '1,000',
            'percentage' => 20,
            'due_upon' => 'Contract signature',
        ]);

        ProcurementRequestRetention::query()->create([
            'procurement_request_id' => $request->id,
            'sort_order' => 0,
            'retention_percent' => 5,
            'release_period' => '1 year',
        ]);

        $snapshot = app(ProcurementRequestCommercialTermsForPurchaseOrder::class)->snapshot($request);

        $this->assertStringContainsString('Advance', $snapshot['payment_terms']);
        $this->assertStringContainsString('20%', $snapshot['payment_terms']);
        $this->assertTrue($snapshot['has_payment_terms']);
        $this->assertTrue($snapshot['has_retention']);
        $this->assertTrue($snapshot['has_maintenance']);
        $this->assertTrue($snapshot['after_sale_service_applicable']);
        $this->assertSame('2.0', (string) $snapshot['warranty_years']);
        $this->assertSame('Parts and labor', $snapshot['warranty_coverage']);
    }

    public function test_normalize_header_casts_visibility_flags_and_retentions(): void
    {
        $validated = [
            'show_payment_terms' => '0',
            'show_retention' => '0',
            'show_maintenance' => '1',
            'after_sale_service_applicable' => '1',
            'warranty_years' => '2.5',
            'retentions' => [
                ['retention_percent' => '5', 'release_period' => '1 year'],
                ['retention_percent' => '', 'release_period' => ''],
            ],
        ];

        ProcurementRequestCommercialTermsForPurchaseOrder::normalizeHeader($validated);

        $this->assertFalse($validated['show_payment_terms']);
        $this->assertFalse($validated['show_retention']);
        $this->assertTrue($validated['show_maintenance']);
        $this->assertTrue($validated['after_sale_service_applicable']);
        $this->assertSame('2.5', $validated['warranty_years']);
        $this->assertCount(1, $validated['retentions']);
        $this->assertSame(5.0, $validated['retentions'][0]['retention_percent']);
    }
}
