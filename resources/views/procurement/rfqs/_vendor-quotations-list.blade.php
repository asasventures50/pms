@if ($rfq->vendorQuotations->isNotEmpty())
        <div class="mt-4 overflow-x-auto rounded-lg border border-slate-200 bg-white">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase text-slate-600">
                <tr>
                    <th class="px-3 py-2 text-left">Quotation No.</th>
                    <th class="px-3 py-2 text-left">Vendor</th>
                    <th class="px-3 py-2 text-right">Grand total</th>
                    <th class="px-3 py-2 text-left">Date</th>
                    <th class="px-3 py-2"></th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @foreach ($rfq->vendorQuotations as $quotation)
                    <tr>
                        <td class="px-3 py-2 font-mono">{{ $quotation->quotation_number }}</td>
                        <td class="px-3 py-2">{{ $quotation->vendor_company_name ?? $quotation->vendor?->name ?? '—' }}</td>
                        <td class="px-3 py-2 text-right font-mono">{{ number_format($quotation->grand_total ?? 0, 2) }}</td>
                        <td class="px-3 py-2">{{ $quotation->created_at?->format('Y-m-d') ?? '—' }}</td>
                        <td class="px-3 py-2 text-right">
                            @if (auth()->user()->hasPermission('vendor-quotations.view'))
                                <a href="{{ route('rfqs.quotations.show', [$rfq, $quotation]) }}" class="font-medium text-slate-700 hover:text-slate-900">View</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
@else
    <p class="mt-4 text-sm text-slate-600">No vendor quotations yet.</p>
@endif
