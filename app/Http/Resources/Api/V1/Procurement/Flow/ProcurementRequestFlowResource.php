<?php

namespace App\Http\Resources\Api\V1\Procurement\Flow;

use App\Models\Procurement\Invoices\Invoice;
use App\Models\Procurement\ProcurementRequests\ProcurementRequest;
use App\Models\Procurement\PurchaseOrders\PurchaseOrder;
use App\Models\Procurement\Rfqs\Rfq;
use App\Services\Procurement\Flow\ProcurementRequestFlowView;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProcurementRequestFlowResource extends JsonResource
{
    public function __construct(
        private readonly ProcurementRequest $procurementRequest,
        private readonly ?ProcurementRequestFlowView $flow,
        private readonly bool $viewAll,
    ) {
        parent::__construct($procurementRequest);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $status = $this->procurementRequest->status;
        $flow = $this->flow;

        return [
            'id' => $this->procurementRequest->id,
            'request_number' => $this->procurementRequest->request_number,
            'status' => $status instanceof \BackedEnum ? $status->value : $status,
            'requested_at' => $this->procurementRequest->requested_at?->format('Y-m-d'),
            'project' => $this->procurementRequest->project ? [
                'id' => $this->procurementRequest->project->id,
                'code' => $this->procurementRequest->project->code,
                'name' => $this->procurementRequest->project->name,
            ] : null,
            'creator' => $this->when($this->viewAll, fn () => $this->procurementRequest->creator ? [
                'id' => $this->procurementRequest->creator->id,
                'name' => $this->procurementRequest->creator->name,
            ] : null),
            'flow' => $flow === null ? null : [
                'active_stage' => $flow->activeStage->value,
                'status_summary' => $flow->statusSummary(),
                'rfq_count' => $flow->rfqCount,
                'quotation_count' => $flow->quotationCount,
                'po_count' => $flow->poCount,
                'invoice_count' => $flow->invoiceCount,
                'has_selection' => $flow->hasSelection,
                'stages' => FlowStageResource::collection($flow->stages),
                'rfqs' => $flow->rfqs->map(fn (Rfq $rfq) => [
                    'id' => $rfq->id,
                    'rfq_number' => $rfq->rfq_number,
                    'selected_vendor_quotation_id' => $rfq->selected_vendor_quotation_id,
                ])->values(),
                'purchase_orders' => $flow->purchaseOrders->map(fn (PurchaseOrder $po) => [
                    'id' => $po->id,
                    'po_number' => $po->po_number,
                    'status' => $po->status instanceof \BackedEnum ? $po->status->value : $po->status,
                ])->values(),
                'invoices' => $flow->invoices->map(fn (Invoice $invoice) => [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'source' => $invoice->source instanceof \BackedEnum ? $invoice->source->value : $invoice->source,
                ])->values(),
            ],
        ];
    }
}
