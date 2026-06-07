@php
    use App\Support\Procurement\ProcurementScopeType;

    $itemCount = $procurementRequest->items->count();
    $emptyRowCount = max(0, $minItemRows - $itemCount);
@endphp

<table class="po-items-table pr-items-table">
    <colgroup>
        <col class="col-line">
        <col class="col-project">
        <col class="col-category">
        <col class="col-scope">
        <col class="col-desc">
        <col class="col-sow">
        <col class="col-unit">
        <col class="col-qty">
    </colgroup>
    <thead>
    <tr class="po-thead-meta">
        <th colspan="8">
            P.R. {{ $procurementRequest->request_number }}
            @if ($procurementRequest->requested_at)
                · {{ $procurementRequest->requested_at->format('d-m-Y') }}
            @endif
            @if ($procurementRequest->requestor_name ?? $procurementRequest->creator?->name)
                · {{ $procurementRequest->requestor_name ?? $procurementRequest->creator?->name }}
            @endif
        </th>
    </tr>
    <tr>
        <th class="col-line">Line</th>
        <th class="col-project">Project /<br>Zone</th>
        <th class="col-category">Category /<br>Sub category</th>
        <th class="col-scope">Scope<br>type</th>
        <th class="col-desc">Item or service<br>description</th>
        <th class="col-sow">Scope of<br>work</th>
        <th class="col-unit">Unit</th>
        <th class="col-qty">Qty</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($procurementRequest->items as $index => $line)
        @php
            $lineNo = $line->line_number;
            if (($lineNo === null || $lineNo === '') && $procurementRequest) {
                $lineNo = \App\Services\Procurement\ProcurementRequests\ProcurementRequestLineNumberFormatter::format(
                    $procurementRequest->request_number,
                    $index
                );
            }
            $projectLabel = $line->project
                ? trim($line->project->code.' — '.$line->project->name)
                : '';
            $zoneLabel = $line->zone
                ? trim($line->zone->code.' — '.$line->zone->name)
                : '';
            $projectZone = collect([$projectLabel, $zoneLabel])->filter()->implode("\n");
            $categoryLabel = collect([$line->category, $line->subcategory])->filter()->implode("\n");
            $scopeType = str_replace(', ', "\n", ProcurementScopeType::display($line->scope_type));
        @endphp
        <tr>
            <td class="po-cell-item">{{ $lineNo }}</td>
            <td class="po-cell-text pr-cell-stack">{{ $projectZone }}</td>
            <td class="po-cell-text pr-cell-stack">{{ $categoryLabel }}</td>
            <td class="po-cell-text pr-cell-scope">{{ $scopeType }}</td>
            <td class="po-cell-text">{{ $line->description }}</td>
            <td class="po-cell-text">{{ $line->scope_of_work }}</td>
            <td class="po-cell-num">{{ $line->unit }}</td>
            <td class="po-cell-num po-cell-qty">{{ number_format($line->quantity, 3) }}</td>
        </tr>
    @endforeach
    @for ($i = 0; $i < $emptyRowCount; $i++)
        <tr>
            <td>&nbsp;</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    @endfor
    @if ($itemCount === 0 && $emptyRowCount === 0)
        @for ($i = 0; $i < $minItemRows; $i++)
            <tr>
                <td>&nbsp;</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        @endfor
    @endif
    </tbody>
</table>

@foreach ($procurementRequest->items as $index => $line)
    @php
        $lineNo = $line->line_number;
        if (($lineNo === null || $lineNo === '') && $procurementRequest) {
            $lineNo = \App\Services\Procurement\ProcurementRequests\ProcurementRequestLineNumberFormatter::format(
                $procurementRequest->request_number,
                $index
            );
        }
        $justification = trim((string) ($line->justification ?? ''));
        $deliveryDate = $line->required_delivery_date?->format('d-m-Y') ?? '';
        $deliveryLocation = trim((string) ($line->delivery_location ?? ''));
        $flexible = $line->flexible_delivery_date ? 'Yes' : 'No';
        $documentNames = $line->documents->pluck('file_name')->filter()->values();
        $hasDeliveryMeta = $deliveryDate !== '' || $deliveryLocation !== '' || $documentNames->isNotEmpty();
    @endphp
    @if ($justification !== '' || $hasDeliveryMeta)
        <section class="pr-line-details">
            <div class="pr-line-details-title">Line {{ $lineNo }}</div>
            @if ($justification !== '')
                <div class="po-field-block">
                    <div class="po-field-label">Justification</div>
                    <div class="po-field-value">{{ $justification }}</div>
                </div>
            @endif
            @if ($hasDeliveryMeta)
                <div class="pr-line-delivery-meta">
                    @if ($deliveryDate !== '' || $deliveryLocation !== '')
                        <span class="pr-line-meta-label">Delivery:</span>
                        @if ($deliveryDate !== '')
                            {{ $deliveryDate }}
                        @endif
                        @if ($deliveryLocation !== '')
                            @ {{ $deliveryLocation }}
                        @endif
                        <span class="pr-line-meta-note">(Flexible: {{ $flexible }})</span>
                    @endif
                    @if ($documentNames->isNotEmpty())
                        @if ($deliveryDate !== '' || $deliveryLocation !== '')
                            <span class="pr-line-meta-sep">|</span>
                        @endif
                        <span class="pr-line-meta-label">Documents:</span> {{ $documentNames->implode(', ') }}
                    @endif
                </div>
            @endif
        </section>
    @endif
@endforeach
