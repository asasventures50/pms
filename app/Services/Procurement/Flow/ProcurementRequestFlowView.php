<?php

namespace App\Services\Procurement\Flow;

use App\Enums\Procurement\Flow\FlowStageKey;
use App\Models\Procurement\Invoices\Invoice;
use App\Models\Procurement\ProcurementRequests\ProcurementRequest;
use App\Models\Procurement\PurchaseOrders\PurchaseOrder;
use App\Models\Procurement\Rfqs\Rfq;
use Illuminate\Database\Eloquent\Collection;

final class ProcurementRequestFlowView
{
    /**
     * @param  list<FlowStage>  $stages
     * @param  Collection<int, Rfq>  $rfqs
     * @param  Collection<int, PurchaseOrder>  $purchaseOrders
     * @param  Collection<int, Invoice>  $invoices
     */
    public function __construct(
        public ProcurementRequest $procurementRequest,
        public FlowStageKey $activeStage,
        public array $stages,
        public Collection $rfqs,
        public Collection $purchaseOrders,
        public Collection $invoices,
        public int $rfqCount,
        public int $quotationCount,
        public int $poCount,
        public int $invoiceCount,
        public bool $hasSelection,
    ) {}

    public function statusSummary(): string
    {
        $stage = collect($this->stages)->first(fn (FlowStage $s) => $s->key === $this->activeStage);

        $parts = [$this->activeStage->statusLabel()];

        if ($this->activeStage === FlowStageKey::Quotations && $this->quotationCount > 0) {
            $parts[] = $this->quotationCount.' '.($this->quotationCount === 1 ? 'quotation' : 'quotations');
        } elseif ($this->activeStage === FlowStageKey::Rfq && $this->rfqCount > 0) {
            $parts[] = $this->rfqCount.' '.($this->rfqCount === 1 ? 'RFQ' : 'RFQs');
        } elseif ($this->activeStage === FlowStageKey::Po && $this->poCount > 0) {
            $parts[] = $this->poCount.' '.($this->poCount === 1 ? 'PO' : 'POs');
        } elseif ($this->activeStage === FlowStageKey::Invoice && $this->invoiceCount > 0) {
            $parts[] = $this->invoiceCount.' '.($this->invoiceCount === 1 ? 'invoice' : 'invoices');
        } elseif ($stage?->detail) {
            $parts[] = $stage->detail;
        }

        return implode(' · ', $parts);
    }
}
