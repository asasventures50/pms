@php
    use App\Enums\Procurement\PrCompany;

    $docNumber = $docNumber ?? $procurementRequest?->request_number ?? ($nextCode ?? '');
    $selectedCompanyKey = old('company_key', $formDefaults['company_key'] ?? $procurementRequest?->company_key ?? PrCompany::AsasVentures->value);
    $selectedCompany = PrCompany::resolve($selectedCompanyKey);
@endphp
<table class="w-full border-2 border-slate-900 border-collapse text-slate-900">
    <tr>
        <td class="w-[22%] min-h-[5rem] border border-slate-900 p-2 align-middle text-center">
            @if (request()->routeIs('procurement-requests.create', 'procurement-requests.edit'))
                <div id="pr-company-logo-preview" class="flex min-h-[4rem] items-center justify-center">
                    <img src="{{ $selectedCompany->logoUrl() }}" alt="{{ $selectedCompany->label() }}"
                         class="max-h-16 max-w-full object-contain"
                         data-pr-company-logo
                         @unless ($selectedCompany->logoExists())
                             onerror="this.style.display='none';this.nextElementSibling.style.display='block';"
                         @endunless>
                    <div class="text-xs font-bold leading-tight text-slate-800" data-pr-company-logo-fallback
                         @if ($selectedCompany->logoExists()) style="display:none;" @endif>
                        {!! $selectedCompany->logoFallbackHtml() !!}
                    </div>
                </div>
            @elseif ($procurementRequest?->exists)
                @php $displayCompany = PrCompany::resolve($procurementRequest->company_key); @endphp
                <div class="flex min-h-[4rem] items-center justify-center">
                    <img src="{{ $displayCompany->logoUrl() }}" alt="{{ $displayCompany->label() }}"
                         class="max-h-16 max-w-full object-contain"
                         @unless ($displayCompany->logoExists())
                             onerror="this.style.display='none';this.nextElementSibling.style.display='block';"
                         @endunless>
                    <div class="text-xs font-bold leading-tight text-slate-800"
                         @if ($displayCompany->logoExists()) style="display:none;" @endif>
                        {!! $displayCompany->logoFallbackHtml() !!}
                    </div>
                </div>
            @endif
        </td>
        <td class="w-[56%] border border-slate-900 p-4 text-center align-middle">
            <p class="text-xl font-bold tracking-tight sm:text-2xl">Procurement Request</p>
            @if (request()->routeIs('procurement-requests.create', 'procurement-requests.edit'))
                <div class="mt-3 text-left sm:mx-auto sm:max-w-xs">
                    <label for="company_key" class="block text-xs font-medium uppercase tracking-wide text-slate-500">
                        Company <span class="text-red-600">*</span>
                    </label>
                    <select name="company_key" id="company_key" required
                            class="admin-filter-control mt-1 w-full @error('company_key') border-red-500 @enderror"
                            data-pr-company-select>
                        @foreach (PrCompany::cases() as $company)
                            <option value="{{ $company->value }}"
                                    data-logo-url="{{ $company->logoUrl() }}"
                                    data-logo-fallback="{{ $company->logoFallbackHtml() }}"
                                    @selected((string) $selectedCompanyKey === $company->value)>
                                {{ $company->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('company_key')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            @elseif ($procurementRequest?->exists)
                <p class="mt-2 text-sm text-slate-600">{{ PrCompany::resolve($procurementRequest->company_key)->label() }}</p>
            @endif
        </td>
        <td class="w-[22%] border border-slate-900 p-2 text-center align-middle">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Doc No.</p>
            <p id="pr-doc-number-preview"
               data-preview="{{ $nextCode ?? '' }}"
               class="mt-1 font-mono text-sm font-semibold text-slate-900">{{ $docNumber ?: '—' }}</p>
        </td>
    </tr>
</table>
