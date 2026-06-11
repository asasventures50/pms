<?php

namespace Tests\Unit;

use App\Models\Procurement\ProcurementRequests\ProcurementRequest;
use App\Models\Procurement\ProcurementRequests\ProcurementRequestDocument;
use App\Models\Procurement\ProcurementRequests\ProcurementRequestItem;
use App\Models\Procurement\PurchaseOrders\PurchaseOrder;
use App\Models\Procurement\PurchaseOrders\PurchaseOrderItem;
use App\Services\Procurement\PurchaseOrders\PurchaseOrderProcurementRequestContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseOrderProcurementRequestContextTest extends TestCase
{
    use RefreshDatabase;

    public function test_resolve_includes_procurement_type_and_supporting_documents_from_linked_pr(): void
    {
        $request = ProcurementRequest::query()->create([
            'request_number' => 'PR-PO-001',
            'procurement_types' => ['purchase', 'rental'],
            'geographic_scopes' => ['local'],
        ]);

        $includedLine = ProcurementRequestItem::query()->create([
            'procurement_request_id' => $request->id,
            'sort_order' => 0,
            'line_number' => 'L-1',
            'description' => 'Included line',
        ]);

        $excludedLine = ProcurementRequestItem::query()->create([
            'procurement_request_id' => $request->id,
            'sort_order' => 1,
            'line_number' => 'L-2',
            'description' => 'Excluded line',
        ]);

        ProcurementRequestDocument::query()->create([
            'procurement_request_id' => $request->id,
            'document_type' => 'Specification',
            'file_name' => 'header-spec.pdf',
            'file_path' => 'https://example.com/header-spec.pdf',
        ]);

        ProcurementRequestDocument::query()->create([
            'procurement_request_id' => $request->id,
            'procurement_request_item_id' => $includedLine->id,
            'document_type' => 'Drawing',
            'file_name' => 'line-drawing.pdf',
            'file_path' => 'https://example.com/line-drawing.pdf',
        ]);

        ProcurementRequestDocument::query()->create([
            'procurement_request_id' => $request->id,
            'procurement_request_item_id' => $excludedLine->id,
            'file_name' => 'other-line.pdf',
            'file_path' => 'https://example.com/other-line.pdf',
        ]);

        $purchaseOrder = PurchaseOrder::query()->create([
            'po_number' => 'PO-PO-001',
            'procurement_request_id' => $request->id,
        ]);

        PurchaseOrderItem::query()->create([
            'purchase_order_id' => $purchaseOrder->id,
            'sort_order' => 0,
            'item' => 'L-1',
            'description' => 'Included line',
            'quantity' => 1,
            'unit_price' => 10,
            'line_total' => 10,
        ]);

        $context = PurchaseOrderProcurementRequestContext::resolve(
            $purchaseOrder->load(['items', 'procurementRequest'])
        );

        $this->assertSame('Purchase, Rental', $context['procurement_type']);
        $this->assertSame('Local', $context['geographic_scope']);
        $this->assertCount(2, $context['supporting_documents']);
        $this->assertSame('header-spec.pdf', $context['supporting_documents'][0]['file_name']);
        $this->assertSame('line-drawing.pdf', $context['supporting_documents'][1]['file_name']);
    }
}
