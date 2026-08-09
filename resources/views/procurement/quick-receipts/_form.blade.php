@php
    use App\Enums\Procurement\PrCompany;

    $defaults = $defaults ?? [];
    $receipt = $receipt ?? null;
    $companies = $companies ?? PrCompany::cases();
    $selectedCompanyKey = (string) old('company_key', $defaults['company_key'] ?? PrCompany::AsasVentures->value);
    $selectedCompany = PrCompany::resolve($selectedCompanyKey);
@endphp

<section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="mb-4 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
        Daily limit: <strong>{{ number_format($dailyLimit, 2) }}</strong>
        · Used on selected day: <strong>{{ number_format($spentToday, 2) }}</strong>
        · Remaining: <strong>{{ number_format($remainingToday, 2) }}</strong>
        <span class="block mt-1 text-xs text-slate-500">Pending + approved + signed receipts count toward the limit for the expense date. You can edit until approved.</span>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <div class="md:col-span-2">
            <label for="company_key" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Company <span class="text-red-600">*</span></label>
            <div class="mt-1 flex flex-wrap items-center gap-3">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-gradient-to-b from-white to-slate-50 shadow-sm ring-1 ring-slate-900/5"
                     id="qr-company-logo-preview"
                     title="{{ $selectedCompany->label() }}">
                    <img src="{{ $selectedCompany->logoUrl() }}" alt="{{ $selectedCompany->label() }}"
                         class="max-h-10 max-w-10 object-contain"
                         data-qr-company-logo
                         @unless ($selectedCompany->logoExists())
                             onerror="this.style.display='none';this.nextElementSibling.style.display='block';"
                         @endunless>
                    <div class="px-1 text-center text-[9px] font-bold leading-tight text-slate-700"
                         data-qr-company-logo-fallback
                         @if ($selectedCompany->logoExists()) style="display:none;" @endif>
                        {!! $selectedCompany->logoFallbackHtml() !!}
                    </div>
                </div>
                <div class="min-w-0 flex-1">
                    <select name="company_key" id="company_key" required
                            class="admin-filter-control @error('company_key') border-red-500 @enderror"
                            data-qr-company-select>
                        @foreach ($companies as $company)
                            <option value="{{ $company->value }}"
                                    data-logo-url="{{ $company->logoUrl() }}"
                                    data-logo-fallback="{{ $company->logoFallbackHtml() }}"
                                    data-logo-exists="{{ $company->logoExists() ? '1' : '0' }}"
                                    data-label="{{ $company->label() }}"
                                    @selected($selectedCompanyKey === $company->value)>
                                {{ $company->label() }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-slate-500" data-qr-company-label>{{ $selectedCompany->label() }}</p>
                </div>
            </div>
            @error('company_key')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="md:col-span-2">
            <label for="title" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Title <span class="text-red-600">*</span></label>
            <input type="text" id="title" name="title" required maxlength="255"
                   value="{{ old('title', $defaults['title'] ?? '') }}"
                   class="admin-filter-control mt-1 @error('title') border-red-500 @enderror"
                   placeholder="Annual lawn mowing">
            @error('title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="md:col-span-2">
            <label for="description" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Description</label>
            <textarea id="description" name="description" rows="3"
                      class="admin-filter-control mt-1 @error('description') border-red-500 @enderror"
                      placeholder="Optional details">{{ old('description', $defaults['description'] ?? '') }}</textarea>
            @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="amount" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Amount <span class="text-red-600">*</span></label>
            <input type="number" id="amount" name="amount" required step="0.01" min="0.01"
                   value="{{ old('amount', $defaults['amount'] ?? '') }}"
                   class="admin-filter-control mt-1 @error('amount') border-red-500 @enderror">
            @error('amount')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="currency_code" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Currency</label>
            <input type="text" id="currency_code" name="currency_code" maxlength="3"
                   value="{{ old('currency_code', $defaults['currency_code'] ?? 'USD') }}"
                   class="admin-filter-control mt-1 uppercase @error('currency_code') border-red-500 @enderror"
                   placeholder="USD">
            @error('currency_code')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="expense_date" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Expense date <span class="text-red-600">*</span></label>
            <input type="date" id="expense_date" name="expense_date" required
                   value="{{ old('expense_date', $defaults['expense_date'] ?? '') }}"
                   class="admin-filter-control mt-1 @error('expense_date') border-red-500 @enderror">
            @error('expense_date')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Category <span class="text-red-600">*</span></label>
            <div class="mt-1 @error('category_id') rounded-lg ring-1 ring-red-500 @enderror">
                @include('partials.searchable-select', [
                    'name' => 'category_id',
                    'selectedValue' => old('category_id', $defaults['category_id'] ?? ''),
                    'options' => $categories,
                    'placeholder' => 'Select category…',
                    'searchPlaceholder' => 'Search categories…',
                ])
            </div>
            @error('category_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="md:col-span-2">
            <label for="provider_name" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Provider name</label>
            <input type="text" id="provider_name" name="provider_name" maxlength="255"
                   value="{{ old('provider_name', $defaults['provider_name'] ?? '') }}"
                   class="admin-filter-control mt-1 @error('provider_name') border-red-500 @enderror"
                   placeholder="Optional free-text provider (no vendor registration)">
            @error('provider_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="md:col-span-2">
            <span class="block text-xs font-medium uppercase tracking-wide text-slate-500">Attachment <span class="normal-case text-slate-400">(optional)</span></span>
            <div class="mt-1 flex flex-wrap items-center gap-3">
                <input type="file" id="attachment" name="attachment" accept=".jpg,.jpeg,.png,.webp,.pdf,image/*,application/pdf"
                       class="sr-only @error('attachment') ring-1 ring-red-500 @enderror"
                       data-qr-attachment-input>
                <label for="attachment"
                       class="inline-flex cursor-pointer items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 shadow-sm hover:bg-slate-50">
                    Choose file
                </label>
                <span class="text-sm text-slate-500" data-qr-attachment-name>No file chosen</span>
            </div>
            <p class="mt-1 text-xs text-slate-500">Optional proof — JPG, PNG, WEBP, or PDF, max 10 MB.</p>
            @if ($receipt?->hasAttachment())
                <p class="mt-2 text-sm text-slate-600">
                    Current file:
                    <a href="{{ $receipt->attachmentUrl() }}" target="_blank" rel="noopener"
                       class="font-medium text-slate-800 underline">{{ $receipt->attachment_original_name ?: 'View attachment' }}</a>
                    (upload a new file to replace)
                </p>
            @endif
            @error('attachment')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>
</section>

@once
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const companySelect = document.querySelector('[data-qr-company-select]');
    const companyLogo = document.querySelector('[data-qr-company-logo]');
    const companyLogoFallback = document.querySelector('[data-qr-company-logo-fallback]');
    const companyLabel = document.querySelector('[data-qr-company-label]');
    const logoPreview = document.getElementById('qr-company-logo-preview');

    function syncCompanyLogo() {
        if (!companySelect || !companyLogo) return;

        const option = companySelect.options[companySelect.selectedIndex];
        const logoUrl = option?.dataset.logoUrl || '';
        const fallbackHtml = option?.dataset.logoFallback || '';
        const label = option?.dataset.label || option?.textContent?.trim() || '';

        if (logoPreview && label) {
            logoPreview.setAttribute('title', label);
        }
        if (companyLabel && label) {
            companyLabel.textContent = label;
        }

        companyLogo.alt = label;
        companyLogo.src = logoUrl;
        companyLogo.style.display = '';

        if (companyLogoFallback) {
            companyLogoFallback.innerHTML = fallbackHtml;
            companyLogoFallback.style.display = 'none';
        }

        companyLogo.onerror = function () {
            companyLogo.style.display = 'none';
            if (companyLogoFallback) companyLogoFallback.style.display = 'block';
        };
    }

    companySelect?.addEventListener('change', syncCompanyLogo);

    const attachmentInput = document.querySelector('[data-qr-attachment-input]');
    const attachmentName = document.querySelector('[data-qr-attachment-name]');
    attachmentInput?.addEventListener('change', function () {
        if (!attachmentName) return;
        const file = attachmentInput.files?.[0];
        attachmentName.textContent = file ? file.name : 'No file chosen';
        attachmentName.classList.toggle('text-slate-800', !!file);
        attachmentName.classList.toggle('text-slate-500', !file);
    });
});
</script>
@endpush
@endonce
