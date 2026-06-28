@php
    use App\Services\Procurement\Invoices\InvoiceProjectZoneResolver;

    $invoiceProjectsLabel = $projectZoneResolver instanceof InvoiceProjectZoneResolver
        ? $projectZoneResolver->uniqueProjectsLabelForInvoice($invoice, $poItemsById ?? collect())
        : null;
@endphp

<div class="inv-recipient-block">
    <span class="inv-recipient-label">السيد / السادة:</span>
    <span class="inv-recipient-name">{{ $invoice->recipient_name }}</span>
</div>

@if ($invoiceProjectsLabel)
    <div class="inv-project-block">
        <span class="inv-project-label">المشروع:</span>
        <span class="inv-project-name">{{ $invoiceProjectsLabel }}</span>
    </div>
@endif
