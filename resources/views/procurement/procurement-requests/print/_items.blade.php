@php
    use App\Support\Procurement\ProcurementScopeType;
@endphp

<table class="po-items-table pr-items-table">
    <thead>
    <tr class="po-thead-meta">
        <th colspan="13">
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
        <th>Line</th>
        <th>Project /<br>Zone</th>
        <th>Category /<br>Sub category</th>
        <th>Scope<br>type</th>
        <th>Item or service<br>description</th>
        <th>Scope of<br>work</th>
        <th>Justifi-<br>cation</th>
        <th>Delivery<br>date</th>
        <th>Delivery<br>location</th>
        <th>Flex</th>
        <th>Documents</th>
        <th>Unit</th>
        <th>Qty</th>
    </tr>
    </thead>
    <tbody>
    @forelse ($procurementRequest->items as $index => $line)
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
            $justification = trim((string) ($line->justification ?? ''));
            $deliveryDate = $line->required_delivery_date?->format('d-m-Y') ?? '';
            $deliveryLocation = trim((string) ($line->delivery_location ?? ''));
            $hasDelivery = $deliveryDate !== '' || $deliveryLocation !== '';
            $flexLabel = $hasDelivery ? ($line->flexible_delivery_date ? 'Yes' : 'No') : '';
        @endphp
        <tr>
            <td class="po-cell-item">{{ $lineNo }}</td>
            <td class="po-cell-text pr-cell-stack">{{ $projectZone }}</td>
            <td class="po-cell-text pr-cell-stack">{{ $categoryLabel }}</td>
            <td class="po-cell-text pr-cell-scope">{{ $scopeType }}</td>
            <td class="po-cell-text pr-cell-wrap">{{ $line->description }}</td>
            <td class="po-cell-text pr-cell-wrap">{{ $line->scope_of_work }}</td>
            <td class="po-cell-text pr-cell-wrap">{{ $justification }}</td>
            <td class="po-cell-text pr-cell-delivery">{{ $deliveryDate }}</td>
            <td class="po-cell-text pr-cell-wrap">{{ $deliveryLocation }}</td>
            <td class="po-cell-num">{{ $flexLabel }}</td>
            <td class="po-cell-text pr-cell-documents">
                @foreach ($line->documents as $document)
                    @if ($document->url)
                        <a href="{{ $document->url }}" target="_blank" rel="noopener" class="pr-doc-link">{{ $document->file_name }}</a>@if (! $loop->last)<br>@endif
                    @else
                        {{ $document->file_name }}@if (! $loop->last)<br>@endif
                    @endif
                @endforeach
            </td>
            <td class="po-cell-num">{{ $line->unit }}</td>
            <td class="po-cell-num po-cell-qty">{{ number_format($line->quantity, 3) }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="13" class="pr-empty-table">No line items.</td>
        </tr>
    @endforelse
    </tbody>
</table>
