@php
    use App\Enums\Procurement\Rfqs\RfqVendorQuotationInviteLocale;
    use App\Enums\Procurement\Rfqs\RfqVendorQuotationInviteStatus;

    $canGenerateInvite = auth()->user()->hasPermission('vendor-quotations.create')
        || auth()->user()->hasPermission('rfqs.update');
    $invites = $rfq->vendorQuotationInvites ?? collect();
    $generatedUrl = session('generated_vendor_quotation_invite_url');
@endphp

@if ($canGenerateInvite && $rfq->items->isNotEmpty())
    <section class="mx-auto mb-6 max-w-4xl rounded-xl border border-sky-300 bg-sky-50 p-4 shadow-sm print:hidden">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900">External vendor quotation link</h2>
                <p class="mt-1 text-sm text-slate-600">
                    Generate a secure link for a vendor to fill their quotation. Copy it and send via WhatsApp.
                </p>
            </div>
        </div>

        @if ($errors->has('vendor_id') || $errors->has('ui_locale') || $errors->has('vendor_quotation_invite'))
            <div class="mt-3 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-900">
                {{ $errors->first('vendor_quotation_invite') ?: ($errors->first('vendor_id') ?: $errors->first('ui_locale')) }}
            </div>
        @endif

        @if ($generatedUrl)
            <div class="mt-4 rounded-lg border border-emerald-300 bg-white p-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-800">Link ready — copy &amp; send</p>
                <div class="mt-2 flex flex-col gap-2 sm:flex-row sm:items-center">
                    <input type="text"
                           readonly
                           id="generated-vendor-quote-link"
                           value="{{ $generatedUrl }}"
                           class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 font-mono text-xs text-slate-800">
                    <button type="button"
                            id="copy-vendor-quote-link"
                            class="inline-flex shrink-0 items-center justify-center rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">
                        Copy link
                    </button>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('rfqs.vendor-quotation-invites.store', $rfq) }}" class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @csrf
            <label class="block sm:col-span-2 lg:col-span-1">
                <span class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Vendor</span>
                <select name="vendor_id" required
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900">
                    <option value="">Select vendor…</option>
                    @foreach ($vendorSelectOptions ?? [] as $option)
                        <option value="{{ $option['id'] }}" @selected((string) old('vendor_id') === (string) $option['id'])>
                            {{ $option['label'] }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label class="block">
                <span class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">UI language</span>
                <select name="ui_locale" required
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900">
                    @foreach (RfqVendorQuotationInviteLocale::cases() as $localeOption)
                        <option value="{{ $localeOption->value }}"
                            @selected(old('ui_locale', RfqVendorQuotationInviteLocale::VendorChoice->value) === $localeOption->value)>
                            {{ $localeOption->label() }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label class="flex items-end gap-2 pb-2">
                <input type="checkbox"
                       name="include_terms"
                       value="1"
                       class="size-4 rounded border-slate-300 text-sky-700 focus:ring-sky-600"
                       @checked(old('include_terms'))>
                <span class="text-sm text-slate-800">Include terms &amp; conditions</span>
            </label>

            <div class="flex items-end">
                <button type="submit"
                        class="inline-flex w-full items-center justify-center rounded-lg border-2 border-sky-600 bg-sky-400 px-4 py-2.5 text-sm font-bold text-slate-900 shadow hover:bg-sky-300">
                    Generate link
                </button>
            </div>
        </form>

        @if ($invites->isNotEmpty())
            <div class="mt-4 overflow-x-auto rounded-lg border border-sky-200 bg-white">
                <table class="min-w-full text-sm">
                    <thead class="bg-sky-50 text-xs font-semibold uppercase text-slate-600">
                    <tr>
                        <th class="px-3 py-2 text-left">Vendor</th>
                        <th class="px-3 py-2 text-left">Language</th>
                        <th class="px-3 py-2 text-left">Terms</th>
                        <th class="px-3 py-2 text-left">Status</th>
                        <th class="px-3 py-2 text-left">Created</th>
                        <th class="px-3 py-2 text-right">Link</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                    @foreach ($invites as $inviteRow)
                        <tr>
                            <td class="px-3 py-2">{{ $inviteRow->vendor?->name ?? '—' }}</td>
                            <td class="px-3 py-2">{{ $inviteRow->ui_locale?->label() ?? '—' }}</td>
                            <td class="px-3 py-2">{{ $inviteRow->include_terms ? 'Yes' : 'No' }}</td>
                            <td class="px-3 py-2">
                                @php $status = $inviteRow->status ?? RfqVendorQuotationInviteStatus::Pending; @endphp
                                <span @class([
                                    'rounded-full px-2 py-0.5 text-[11px] font-bold uppercase',
                                    'bg-amber-100 text-amber-900' => $status === RfqVendorQuotationInviteStatus::Pending,
                                    'bg-emerald-100 text-emerald-900' => $status === RfqVendorQuotationInviteStatus::Submitted,
                                    'bg-slate-100 text-slate-700' => $status === RfqVendorQuotationInviteStatus::Revoked,
                                ])>
                                    {{ $status->label() }}
                                </span>
                            </td>
                            <td class="px-3 py-2">{{ $inviteRow->created_at?->format('Y-m-d H:i') ?? '—' }}</td>
                            <td class="px-3 py-2 text-right">
                                <button type="button"
                                        class="js-copy-invite-link font-medium text-sky-800 hover:text-sky-950"
                                        data-link="{{ $inviteRow->publicUrl() }}">
                                    Copy
                                </button>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    @push('scripts')
        <script>
            (function () {
                function copyText(value, button) {
                    if (!value) return;
                    const label = button ? button.textContent : '';
                    const done = function () {
                        if (!button) return;
                        button.textContent = 'Copied';
                        setTimeout(function () { button.textContent = label || 'Copy'; }, 1500);
                    };
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(value).then(done).catch(function () {
                            window.prompt('Copy this link:', value);
                        });
                    } else {
                        window.prompt('Copy this link:', value);
                    }
                }

                const mainCopy = document.getElementById('copy-vendor-quote-link');
                const mainInput = document.getElementById('generated-vendor-quote-link');
                if (mainCopy && mainInput) {
                    mainCopy.addEventListener('click', function () {
                        copyText(mainInput.value, mainCopy);
                    });
                }

                document.querySelectorAll('.js-copy-invite-link').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        copyText(btn.getAttribute('data-link'), btn);
                    });
                });
            })();
        </script>
    @endpush
@endif
