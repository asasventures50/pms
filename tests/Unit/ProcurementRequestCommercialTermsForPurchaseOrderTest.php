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

    public function test_snapshot_formats_payment_terms_retention_and_insurance_from_pr(): void
    {
        $request = ProcurementRequest::query()->create([
            'request_number' => 'PR-TEST-001',
            'primary_insurance_applicable' => true,
            'primary_insurance_requirements' => 'Primary cover',
            'final_insurance_applicable' => false,
            'final_insurance_requirements' => null,
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
        $this->assertTrue($snapshot['has_retention']);
        $this->assertTrue($snapshot['has_insurance']);
        $this->assertSame('Primary cover', $snapshot['primary_insurance_requirements']);
        $this->assertFalse($snapshot['final_insurance_applicable']);
    }

    public function test_normalize_header_casts_visibility_flags_and_retentions(): void
    {
        $validated = [
            'show_retention' => '0',
            'show_insurance' => '1',
            'primary_insurance_applicable' => '1',
            'retentions' => [
                ['retention_percent' => '5', 'release_period' => '1 year'],
                ['retention_percent' => '', 'release_period' => ''],
            ],
        ];

        ProcurementRequestCommercialTermsForPurchaseOrder::normalizeHeader($validated);

        $this->assertFalse($validated['show_retention']);
        $this->assertTrue($validated['show_insurance']);
        $this->assertTrue($validated['primary_insurance_applicable']);
        $this->assertCount(1, $validated['retentions']);
        $this->assertSame(5.0, $validated['retentions'][0]['retention_percent']);
    }
}
