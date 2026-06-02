@php
    use App\Enums\Procurement\Rfqs\RfqTermsLocale;

    $poTerms = $poTerms ?? ['general' => [], 'custom_rows' => []];
    $generalTerms = $poTerms['general'] ?? [];
    $customRows = $poTerms['custom_rows'] ?? [];
    $terms = $terms ?? [];
    $editable = $editable ?? false;
    $termsLocale = old('terms_locale', $po?->terms_locale ?? RfqTermsLocale::default()->value);
@endphp

<section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm" @if ($editable) id="po-terms-section" @endif>
    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="border-b border-slate-100 pb-3 text-base font-semibold text-slate-900">Terms &amp; conditions</h2>
            @if ($editable)
                <p class="mt-2 text-xs text-slate-500">Loaded from the same library as RFQs. Maintenance and dismantling scope terms are included when the related order-term dates are set.</p>
            @endif
        </div>
        @if ($editable && auth()->user()->hasPermission('rfq-terms.view'))
            <a href="{{ route('rfq-terms.index') }}" class="text-xs font-medium text-slate-600 hover:text-slate-900 print:hidden">Manage general terms</a>
        @endif
    </div>

    @if ($editable)
        <div class="mt-4 print:hidden">
            <span class="block text-xs font-semibold uppercase tracking-wide text-slate-600">Terms language</span>
            <div class="mt-2 flex flex-wrap gap-4 text-sm text-slate-800">
                @foreach (RfqTermsLocale::cases() as $locale)
                    <label class="inline-flex items-center gap-2">
                        <input type="radio" name="terms_locale" value="{{ $locale->value }}"
                               class="border-slate-300 text-slate-900 focus:ring-slate-500 po-terms-locale"
                               @checked($termsLocale === $locale->value)>
                        <span>{{ $locale->label() }}</span>
                    </label>
                @endforeach
            </div>
            @error('terms_locale')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        @error('terms_custom')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror

        <div class="mt-4">
            <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-600">General terms</h3>
            <ul id="po-general-terms-list" class="mt-2 list-none space-y-1.5 text-sm text-slate-800">
                @forelse ($generalTerms as $term)
                    <li class="po-general-term-row flex gap-2">
                        <span class="shrink-0">-</span>
                        <span class="min-w-0 flex-1" @if($termsLocale === 'ar') dir="rtl" @endif>{{ $term }}</span>
                    </li>
                @empty
                    <li id="po-general-terms-empty" class="text-slate-500">Company-wide terms load automatically. Set handover or dismantling dates to include related scope terms.</li>
                @endforelse
            </ul>
        </div>

        @include('procurement.partials._additional-custom-terms', [
            'customRows' => $customRows,
            'termsLocale' => $termsLocale,
            'listId' => 'po-custom-terms-list',
            'templateId' => 'po-custom-term-template',
            'addButtonId' => 'po-add-custom-term',
            'rowClass' => 'po-custom-term-row',
            'removeClass' => 'po-remove-custom-term',
            'inputClass' => 'admin-filter-control',
            'scopeLabel' => 'this PO only',
            'headingTag' => 'h3',
        ])
    @else
        @include('procurement.partials._terms-display-list', [
            'terms' => $terms,
            'termsLocale' => $po->terms_locale ?? 'en',
            'compact' => true,
        ])
    @endif
</section>
