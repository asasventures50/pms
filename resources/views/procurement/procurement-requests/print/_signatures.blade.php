@php
    $formData = $formData ?? [];
@endphp

<div class="pr-print-closing-block">
    <div class="po-section-title">Compliance &amp; approvals</div>
    <p class="pr-closing-nda">
        <strong>NDA required:</strong>
        @if ($procurementRequest->nda_required === null)—@elseif ($procurementRequest->nda_required)Yes@else No @endif
    </p>

    <table class="po-items-table pr-items-table pr-approvals-table">
        <colgroup>
            <col class="pr-approvals-col-role">
            <col class="pr-approvals-col-name">
            <col class="pr-approvals-col-signature">
            <col class="pr-approvals-col-date">
        </colgroup>
        <thead>
        <tr>
            <th>Role</th>
            <th>Name</th>
            <th>Signature</th>
            <th>Date</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($formData['approvals'] ?? [] as $row)
            <tr class="pr-approvals-row">
                <td class="pr-approvals-role">{{ $row['label'] ?? '' }}</td>
                <td class="pr-approvals-name">{{ filled($row['name'] ?? null) ? $row['name'] : '—' }}</td>
                <td class="pr-approvals-signature">{{ filled($row['signature'] ?? null) ? $row['signature'] : '' }}</td>
                <td class="pr-approvals-date">
                    @if (filled($row['signed_at'] ?? null))
                        {{ \Illuminate\Support\Carbon::parse($row['signed_at'])->format('d-m-Y') }}
                    @else
                        —
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
