@php
    use App\Enums\Procurement\VendorQuotations\VendorQuotationDocumentType;
    use Illuminate\Support\Facades\Storage;
@endphp

@if ($columns->isNotEmpty())
    <section class="comparison-supporting-docs mt-8 text-sm">
        <h3 class="text-sm font-bold uppercase tracking-wide text-slate-900">Supporting documents</h3>
        <p class="mt-1 text-xs text-slate-600">Attached files and links per vendor quotation (open from PDF if needed).</p>

        <div class="mt-4 space-y-6">
            @foreach ($columns as $column)
                @php
                    $quotation = $column['quotation'];
                    $documents = $quotation->documents_attached ?? [];
                    $hasDocuments = collect($documents)->contains(
                        fn ($file) => is_array($file) && ! empty($file['file_path'])
                    );
                @endphp

                <div class="rounded-lg border border-slate-200 bg-slate-50/80 p-4 print:rounded-none print:border-slate-300 print:bg-white"
                     data-comparison-quotation="{{ $quotation->id }}">
                    <h4 class="text-xs font-bold uppercase tracking-wide text-slate-800">
                        <span class="font-mono">{{ $quotation->quotation_number }}</span>
                        — {{ $quotation->vendor_company_name ?? $quotation->vendor?->name ?? '—' }}
                    </h4>

                    @if ($hasDocuments)
                        <ul class="mt-3 space-y-2 text-xs">
                            @foreach (VendorQuotationDocumentType::cases() as $docType)
                                @php
                                    $file = $documents[$docType->value] ?? null;
                                    $filePath = is_array($file) ? ($file['file_path'] ?? null) : null;
                                @endphp
                                @if ($filePath)
                                    <li class="flex flex-wrap items-baseline justify-center gap-x-2 gap-y-1 border-b border-slate-200 pb-2 text-center sm:justify-start sm:text-left print:justify-center print:text-center">
                                        <span class="font-medium text-slate-700">{{ $docType->label() }}:</span>
                                        <a href="{{ Storage::disk('s3')->url($filePath) }}"
                                           target="_blank"
                                           rel="noopener"
                                           class="text-blue-700 underline hover:text-blue-900">
                                            {{ $file['file_name'] ?? 'Download' }}
                                        </a>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    @else
                        <p class="mt-2 text-center text-xs text-slate-500 sm:text-left print:text-center">No supporting documents attached.</p>
                    @endif
                </div>
            @endforeach
        </div>
    </section>
@endif
