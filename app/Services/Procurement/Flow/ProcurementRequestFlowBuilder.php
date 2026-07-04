<?php

namespace App\Services\Procurement\Flow;

use App\Enums\Procurement\Flow\FlowStageKey;
use App\Enums\Procurement\Flow\FlowStageState;
use App\Enums\Procurement\ProcurementRequests\ProcurementRequestStatus;
use App\Models\Procurement\Invoices\Invoice;
use App\Models\Procurement\ProcurementRequests\ProcurementRequest;
use App\Models\Procurement\PurchaseOrders\PurchaseOrder;
use App\Models\Procurement\Rfqs\Rfq;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

final class ProcurementRequestFlowBuilder
{
    /**
     * @return array<int, ProcurementRequestFlowView>
     */
    public function buildMany(Collection $procurementRequests): array
    {
        if ($procurementRequests->isEmpty()) {
            return [];
        }

        $procurementRequests->loadMissing('items:id,procurement_request_id');

        $itemIds = $procurementRequests->flatMap(fn (ProcurementRequest $pr) => $pr->items->pluck('id'));

        $rfqsByPrId = $this->loadRfqsGroupedByProcurementRequest($procurementRequests, $itemIds);
        $prIds = $procurementRequests->pluck('id');
        $purchaseOrdersByPrId = $this->loadPurchaseOrdersGroupedByProcurementRequest($prIds);
        $invoicesByPrId = $this->loadInvoicesGroupedByProcurementRequest($prIds);

        $views = [];

        foreach ($procurementRequests as $procurementRequest) {
            $prId = (int) $procurementRequest->id;
            $rfqs = $rfqsByPrId->get($prId, new Collection);
            $purchaseOrders = $purchaseOrdersByPrId->get($prId, new Collection);
            $invoices = $invoicesByPrId->get($prId, new Collection);

            $views[$prId] = $this->assembleView(
                $procurementRequest,
                $rfqs,
                $purchaseOrders,
                $invoices,
            );
        }

        return $views;
    }

    public function build(ProcurementRequest $procurementRequest): ProcurementRequestFlowView
    {
        $views = $this->buildMany(new Collection([$procurementRequest]));

        return $views[(int) $procurementRequest->id];
    }

    /**
     * @param  Collection<int, ProcurementRequest>  $procurementRequests
     * @param  SupportCollection<int, int>  $itemIds
     * @return SupportCollection<int, Collection<int, Rfq>>
     */
    private function loadRfqsGroupedByProcurementRequest(Collection $procurementRequests, SupportCollection $itemIds): SupportCollection
    {
        if ($itemIds->isEmpty()) {
            return collect();
        }

        $itemToPrId = [];

        foreach ($procurementRequests as $procurementRequest) {
            foreach ($procurementRequest->items as $item) {
                $itemToPrId[(int) $item->id] = (int) $procurementRequest->id;
            }
        }

        $rfqs = Rfq::query()
            ->whereHas('items', fn ($query) => $query->whereIn('procurement_request_item_id', $itemIds))
            ->with([
                'items:id,rfq_id,procurement_request_item_id',
                'selectedVendorQuotation:id,quotation_number,vendor_company_name',
            ])
            ->withCount('vendorQuotations')
            ->latest()
            ->get();

        $grouped = collect();

        foreach ($rfqs as $rfq) {
            $prIds = $rfq->items
                ->pluck('procurement_request_item_id')
                ->filter()
                ->map(fn ($itemId) => $itemToPrId[(int) $itemId] ?? null)
                ->filter()
                ->unique();

            foreach ($prIds as $prId) {
                $existing = $grouped->get($prId, new Collection);

                if (! $existing->contains('id', $rfq->id)) {
                    $existing->push($rfq);
                }

                $grouped->put($prId, $existing);
            }
        }

        return $grouped;
    }

    /**
     * @param  SupportCollection<int, int>  $prIds
     * @return SupportCollection<int, Collection<int, PurchaseOrder>>
     */
    private function loadPurchaseOrdersGroupedByProcurementRequest(SupportCollection $prIds): SupportCollection
    {
        if ($prIds->isEmpty()) {
            return collect();
        }

        return PurchaseOrder::query()
            ->whereIn('procurement_request_id', $prIds)
            ->orderByDesc('created_at')
            ->get(['id', 'po_number', 'status', 'procurement_request_id'])
            ->groupBy('procurement_request_id');
    }

    /**
     * @param  SupportCollection<int, int>  $prIds
     * @return SupportCollection<int, Collection<int, Invoice>>
     */
    private function loadInvoicesGroupedByProcurementRequest(SupportCollection $prIds): SupportCollection
    {
        if ($prIds->isEmpty()) {
            return collect();
        }

        $invoices = Invoice::query()
            ->whereHas('purchaseOrders', fn ($query) => $query->whereIn('procurement_request_id', $prIds))
            ->with(['purchaseOrders:id,procurement_request_id'])
            ->orderByDesc('created_at')
            ->get(['id', 'invoice_number', 'source']);

        $grouped = collect();

        foreach ($invoices as $invoice) {
            foreach ($invoice->purchaseOrders as $purchaseOrder) {
                $prId = (int) $purchaseOrder->procurement_request_id;

                if ($prId === 0 || ! $prIds->contains($prId)) {
                    continue;
                }

                $existing = $grouped->get($prId, new Collection);

                if (! $existing->contains('id', $invoice->id)) {
                    $existing->push($invoice);
                }

                $grouped->put($prId, $existing);
            }
        }

        return $grouped;
    }

    /**
     * @param  Collection<int, Rfq>  $rfqs
     * @param  Collection<int, PurchaseOrder>  $purchaseOrders
     * @param  Collection<int, Invoice>  $invoices
     */
    private function assembleView(
        ProcurementRequest $procurementRequest,
        Collection $rfqs,
        Collection $purchaseOrders,
        Collection $invoices,
    ): ProcurementRequestFlowView {
        $quotationCount = (int) $rfqs->sum('vendor_quotations_count');
        $hasSelection = $rfqs->contains(
            fn (Rfq $rfq) => $rfq->selected_vendor_quotation_id !== null
        );

        $activeStage = $this->resolveActiveStage(
            $procurementRequest,
            $rfqs,
            $quotationCount,
            $hasSelection,
            $purchaseOrders,
            $invoices,
        );

        $isCancelled = $procurementRequest->status === ProcurementRequestStatus::Cancelled;

        $stages = array_map(
            fn (FlowStageKey $key) => $this->buildStage(
                $key,
                $activeStage,
                $isCancelled,
                $procurementRequest,
                $rfqs,
                $quotationCount,
                $hasSelection,
                $purchaseOrders,
                $invoices,
            ),
            FlowStageKey::ordered(),
        );

        return new ProcurementRequestFlowView(
            procurementRequest: $procurementRequest,
            activeStage: $activeStage,
            stages: $stages,
            rfqs: $rfqs,
            purchaseOrders: $purchaseOrders,
            invoices: $invoices,
            rfqCount: $rfqs->count(),
            quotationCount: $quotationCount,
            poCount: $purchaseOrders->count(),
            invoiceCount: $invoices->count(),
            hasSelection: $hasSelection,
        );
    }

    /**
     * @param  Collection<int, Rfq>  $rfqs
     * @param  Collection<int, PurchaseOrder>  $purchaseOrders
     * @param  Collection<int, Invoice>  $invoices
     */
    private function resolveActiveStage(
        ProcurementRequest $procurementRequest,
        Collection $rfqs,
        int $quotationCount,
        bool $hasSelection,
        Collection $purchaseOrders,
        Collection $invoices,
    ): FlowStageKey {
        if ($procurementRequest->status === ProcurementRequestStatus::Cancelled) {
            return FlowStageKey::Pr;
        }

        if ($invoices->isNotEmpty()) {
            return FlowStageKey::Invoice;
        }

        if ($purchaseOrders->isNotEmpty()) {
            return FlowStageKey::Po;
        }

        if ($hasSelection) {
            return FlowStageKey::Selection;
        }

        if ($quotationCount > 0) {
            return FlowStageKey::Quotations;
        }

        if ($rfqs->isNotEmpty()) {
            return FlowStageKey::Rfq;
        }

        if (in_array($procurementRequest->status, [
            ProcurementRequestStatus::Submitted,
            ProcurementRequestStatus::Received,
            ProcurementRequestStatus::Closed,
        ], true)) {
            return FlowStageKey::Rfq;
        }

        return FlowStageKey::Pr;
    }

    /**
     * @param  Collection<int, Rfq>  $rfqs
     * @param  Collection<int, PurchaseOrder>  $purchaseOrders
     * @param  Collection<int, Invoice>  $invoices
     */
    private function buildStage(
        FlowStageKey $key,
        FlowStageKey $activeStage,
        bool $isCancelled,
        ProcurementRequest $procurementRequest,
        Collection $rfqs,
        int $quotationCount,
        bool $hasSelection,
        Collection $purchaseOrders,
        Collection $invoices,
    ): FlowStage {
        $state = $this->resolveStageState($key, $activeStage, $isCancelled, $procurementRequest);

        return match ($key) {
            FlowStageKey::Pr => new FlowStage(
                key: $key,
                state: $state,
                label: $key->label(),
                detail: ucfirst($procurementRequest->status->value),
            ),
            FlowStageKey::Rfq => new FlowStage(
                key: $key,
                state: $state,
                label: $key->label(),
                badge: $rfqs->count() > 0 ? $rfqs->count() : null,
                badgeLabel: $rfqs->count() === 1 ? 'RFQ' : 'RFQs',
                detail: $this->rfqDetail($rfqs, $procurementRequest),
            ),
            FlowStageKey::Quotations => new FlowStage(
                key: $key,
                state: $state,
                label: $key->label(),
                badge: $quotationCount > 0 ? $quotationCount : null,
                badgeLabel: $quotationCount === 1 ? 'quotation' : 'quotations',
                detail: $quotationCount > 0
                    ? 'Collecting vendor offers'
                    : ($rfqs->isNotEmpty() ? 'Awaiting vendor offers' : null),
            ),
            FlowStageKey::Selection => new FlowStage(
                key: $key,
                state: $state,
                label: $key->label(),
                badge: $hasSelection ? 1 : null,
                badgeLabel: $hasSelection ? 'selected' : null,
                detail: $hasSelection
                    ? $this->selectionDetail($rfqs)
                    : ($quotationCount > 0 ? 'Awaiting selection' : null),
            ),
            FlowStageKey::Po => new FlowStage(
                key: $key,
                state: $state,
                label: $key->label(),
                badge: $purchaseOrders->count() > 0 ? $purchaseOrders->count() : null,
                badgeLabel: $purchaseOrders->count() === 1 ? 'PO' : 'POs',
                detail: $purchaseOrders->isNotEmpty()
                    ? $purchaseOrders->first()->po_number
                    : ($hasSelection ? 'Awaiting purchase order' : null),
            ),
            FlowStageKey::Invoice => new FlowStage(
                key: $key,
                state: $state,
                label: $key->label(),
                badge: $invoices->count() > 0 ? $invoices->count() : null,
                badgeLabel: $invoices->count() === 1 ? 'invoice' : 'invoices',
                detail: $invoices->isNotEmpty()
                    ? $invoices->first()->invoice_number
                    : ($purchaseOrders->isNotEmpty() ? 'Awaiting invoice' : null),
            ),
        };
    }

    private function resolveStageState(
        FlowStageKey $key,
        FlowStageKey $activeStage,
        bool $isCancelled,
        ProcurementRequest $procurementRequest,
    ): FlowStageState {
        if ($isCancelled && $key === FlowStageKey::Pr) {
            return FlowStageState::Cancelled;
        }

        if ($isCancelled) {
            return FlowStageState::Pending;
        }

        if ($procurementRequest->status === ProcurementRequestStatus::Closed
            && $key->index() <= $activeStage->index()) {
            return FlowStageState::Completed;
        }

        $stageIndex = $key->index();
        $activeIndex = $activeStage->index();

        if ($stageIndex < $activeIndex) {
            return FlowStageState::Completed;
        }

        if ($stageIndex === $activeIndex) {
            return FlowStageState::Active;
        }

        return FlowStageState::Pending;
    }

    /**
     * @param  Collection<int, Rfq>  $rfqs
     */
    private function rfqDetail(Collection $rfqs, ProcurementRequest $procurementRequest): ?string
    {
        if ($rfqs->isNotEmpty()) {
            return $rfqs->first()->rfq_number;
        }

        return match ($procurementRequest->status) {
            ProcurementRequestStatus::Submitted => 'At procurement',
            ProcurementRequestStatus::Received, ProcurementRequestStatus::Closed => 'Awaiting RFQ',
            default => null,
        };
    }

    /**
     * @param  Collection<int, Rfq>  $rfqs
     */
    private function selectionDetail(Collection $rfqs): ?string
    {
        $selectedRfq = $rfqs->first(
            fn (Rfq $rfq) => $rfq->selected_vendor_quotation_id !== null
        );

        if ($selectedRfq?->selectedVendorQuotation) {
            return $selectedRfq->selectedVendorQuotation->quotation_number;
        }

        return 'Quotation selected';
    }
}
