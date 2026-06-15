<?php

namespace Tests\Unit;

use App\Enums\Procurement\PrCompany;
use App\Models\Procurement\ProcurementRequests\ProcurementRequest;
use App\Models\Procurement\PurchaseOrders\PurchaseOrder;
use App\Services\Procurement\PurchaseOrders\ProcurementRequestLinesForPurchaseOrderPresenter;
use App\Services\Procurement\PurchaseOrders\PurchaseOrderBuyerCompanyApplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseOrderBuyerCompanyApplierTest extends TestCase
{
    use RefreshDatabase;

    public function test_apply_to_header_uses_pr_company_when_procurement_request_is_linked(): void
    {
        $request = ProcurementRequest::query()->create([
            'request_number' => 'PR-CO-001',
            'company_key' => PrCompany::QassiounJourney->value,
        ]);

        $header = ['procurement_request_id' => $request->id];

        PurchaseOrderBuyerCompanyApplier::applyToHeader($header);

        $expected = PrCompany::QassiounJourney->toPurchaseOrderHeader();
        $this->assertSame($expected['company_key'], $header['company_key']);
        $this->assertSame($expected['company_name'], $header['company_name']);
        $this->assertSame($expected['company_phone'], $header['company_phone']);
        $this->assertSame($expected['company_email'], $header['company_email']);
        $this->assertSame($expected['company_address'], $header['company_address']);
        $this->assertSame($expected['company_website'], $header['company_website']);
    }

    public function test_presenter_includes_company_payload_from_linked_pr(): void
    {
        $request = ProcurementRequest::query()->create([
            'request_number' => 'PR-CO-002',
            'company_key' => PrCompany::Activation->value,
        ]);

        $payload = app(ProcurementRequestLinesForPurchaseOrderPresenter::class)->present($request);

        $this->assertSame(PrCompany::Activation->value, $payload['company']['key']);
        $this->assertSame('Activation', $payload['company']['label']);
        $this->assertSame('Activation', $payload['company']['buyer']['name']);
    }

    public function test_presenter_includes_currency_code_from_linked_pr(): void
    {
        $request = ProcurementRequest::query()->create([
            'request_number' => 'PR-CUR-001',
            'currency_code' => 'eur',
        ]);

        $payload = app(ProcurementRequestLinesForPurchaseOrderPresenter::class)->present($request);

        $this->assertSame('EUR', $payload['currency_code']);
    }

    public function test_resolve_for_purchase_order_prefers_stored_company_key(): void
    {
        $request = ProcurementRequest::query()->create([
            'request_number' => 'PR-CO-003',
            'company_key' => PrCompany::QassiounJourney->value,
        ]);

        $purchaseOrder = PurchaseOrder::query()->create([
            'po_number' => 'PO-CO-001',
            'procurement_request_id' => $request->id,
            'company_key' => PrCompany::Activation->value,
        ]);

        $this->assertSame(PrCompany::Activation, PurchaseOrderBuyerCompanyApplier::resolveForPurchaseOrder($purchaseOrder));
    }
}
