@php
    use App\Enums\Procurement\Rfqs\RfqTermsLocale;

    $rfqTerms = $rfqTerms ?? ['general' => [], 'custom' => []];
    $generalTerms = $rfqTerms['general'] ?? [];
    $customTerms = $rfqTerms['custom'] ?? [];
    $terms = $terms ?? array_merge($generalTerms, $customTerms);
    $editable = $editable ?? false;
    $termsLocale = old('terms_locale', $rfq?->terms_locale ?? RfqTermsLocale::default()->value);
@endphp

<section class="mt-8" @if ($editable) id="rfq-terms-section" @endif>
    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h3 class="text-sm font-bold uppercase tracking-wide text-slate-900">Terms &amp; conditions</h3>
            @if ($editable)
                <p class="mt-1 text-xs text-slate-500">Company-wide and scope-specific terms (from your line items; a term may apply to several scope types) cannot be removed. You may add RFQ-specific terms below.</p>
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
                               class="border-slate-300 text-slate-900 focus:ring-slate-500 rfq-terms-locale"
                               @checked($termsLocale === $locale->value)>
                        <span>{{ $locale->label() }}</span>
                    </label>
                @endforeach
            </div>
            @error('terms_locale')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        @error('terms_custom')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror

        <div class="mt-4">
            <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-600">General terms <span class="font-normal normal-case text-slate-500">(company + scope)</span></h4>
            <ul id="rfq-general-terms-list" class="mt-2 list-none space-y-1.5 text-sm text-slate-800">
                @forelse ($generalTerms as $term)
                    <li class="rfq-general-term-row flex gap-2">
                        <span class="shrink-0">-</span>
                        <span class="min-w-0 flex-1" @if($termsLocale === 'ar') dir="rtl" @endif>{{ $term }}</span>
                    </li>
                @empty
                    <li id="rfq-general-terms-empty" class="text-slate-500">Company-wide terms load automatically. Select line items to include scope-specific terms.</li>
                @endforelse
            </ul>
        </div>

        <div class="mt-6">
            <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-600">Additional terms <span class="font-normal normal-case text-slate-500">(this RFQ only)</span></h4>
            <ul id="rfq-custom-terms-list" class="mt-2 list-none space-y-2">
                @foreach ($customTerms as $index => $term)
                    <li class="rfq-custom-term-row flex gap-2">
                        <span class="shrink-0 pt-2 text-sm text-slate-800">-</span>
                        <input type="text" name="terms_custom[{{ $index }}]" value="{{ $term }}"
                               class="rfq-doc-field rfq-custom-term-input min-w-0 flex-1 text-sm @error('terms_custom.'.$index) border-red-500 @enderror"
                               @if($termsLocale === 'ar') dir="rtl" @endif>
                        <button type="button" class="rfq-remove-custom-term shrink-0 rounded-lg px-2 py-1 text-sm font-medium text-red-700 hover:bg-red-50 print:hidden">Remove</button>
                    </li>
                @endforeach
            </ul>
            <button type="button" id="rfq-add-custom-term"
                    class="mt-3 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-800 hover:bg-slate-50 print:hidden">
                Add term
            </button>
            <template id="rfq-custom-term-template">
                <li class="rfq-custom-term-row flex gap-2">
                    <span class="shrink-0 pt-2 text-sm text-slate-800">-</span>
                    <input type="text" data-name="terms_custom[]" value=""
                           class="rfq-doc-field rfq-custom-term-input min-w-0 flex-1 text-sm">
                    <button type="button" class="rfq-remove-custom-term shrink-0 rounded-lg px-2 py-1 text-sm font-medium text-red-700 hover:bg-red-50">Remove</button>
                </li>
            </template>
        </div>
    @else
        <ul class="mt-3 list-none space-y-1.5 text-sm text-slate-800">
            @forelse ($terms as $term)
                <li class="flex gap-2">
                    <span class="shrink-0">-</span>
                    <span @if(($rfq->terms_locale ?? 'en') === 'ar') dir="rtl" @endif>{{ $term }}</span>
                </li>
            @empty
                <li class="text-slate-500">No terms specified.</li>
            @endforelse
        </ul>
    @endif
</section>
