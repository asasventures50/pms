@extends('layouts.admin')

@section('title', 'Vendor Details')

@section('content')
    @php
        $e = fn ($v) => $v instanceof \BackedEnum ? $v->value : $v;
        $label = fn ($v) => \Illuminate\Support\Str::headline(str_replace('_', ' ', (string) $e($v)));
    @endphp

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">{{ $vendor->name }}</h1>
            <p class="mt-1 font-mono text-sm text-slate-600">{{ $vendor->vendor_code }}</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('vendors.edit', $vendor) }}"
               class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">Edit</a>
            <a href="{{ route('vendors.index') }}"
               class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">Back to list</a>
        </div>
    </div>

    <div class="space-y-6">
        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="border-b border-slate-100 pb-2 text-base font-semibold text-slate-900">Basic Information</h2>
            <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                <div><dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Language</dt><dd class="mt-1 text-sm text-slate-900">{{ strtoupper($e($vendor->language)) }}</dd></div>
                <div><dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Status</dt><dd class="mt-1 text-sm text-slate-900">{{ $label($vendor->status) }}</dd></div>
                <div class="sm:col-span-2"><dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Description</dt><dd class="mt-1 whitespace-pre-wrap text-sm text-slate-900">{{ $vendor->description ?: '—' }}</dd></div>
                <div class="sm:col-span-2"><dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Notes</dt><dd class="mt-1 whitespace-pre-wrap text-sm text-slate-900">{{ $vendor->notes ?: '—' }}</dd></div>
            </dl>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="border-b border-slate-100 pb-2 text-base font-semibold text-slate-900">Branch locations</h2>
            @if ($vendor->locations->isEmpty())
                <p class="mt-3 text-sm text-slate-500">No branch locations on record.</p>
            @else
                <ul class="mt-4 space-y-4">
                    @foreach ($vendor->locations->sortBy(fn ($l) => [$l->is_primary ? 0 : 1, $l->id]) as $loc)
                        <li class="rounded-lg border border-slate-200 bg-slate-50/50 p-4">
                            <div class="flex flex-wrap items-center gap-2">
                                @if ($loc->is_primary)
                                    <span class="inline-flex rounded-full bg-slate-900 px-2 py-0.5 text-xs font-medium text-white">Primary</span>
                                @endif
                            </div>
                            <dl class="mt-3 grid gap-3 sm:grid-cols-2 text-sm">
                                <div>
                                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Country</dt>
                                    <dd class="mt-1 text-slate-900">
                                        @if ($loc->country)
                                            {{ $loc->country->flag_emoji ? $loc->country->flag_emoji.' ' : '' }}{{ $loc->country->name }}
                                        @else
                                            —
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">City</dt>
                                    <dd class="mt-1 text-slate-900">{{ $loc->city?->name ?? 'Not specified' }}</dd>
                                </div>
                                <div class="sm:col-span-2">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Address</dt>
                                    <dd class="mt-1 whitespace-pre-wrap text-slate-900">{{ $loc->address ?: '—' }}</dd>
                                </div>
                                <div><dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Phone</dt><dd class="mt-1 text-slate-900">{{ $loc->phone ?: '—' }}</dd></div>
                                <div><dt class="text-xs font-medium uppercase tracking-wide text-slate-500">WhatsApp</dt><dd class="mt-1 text-slate-900">{{ $loc->whatsapp ?: '—' }}</dd></div>
                                <div class="sm:col-span-2">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Notes</dt>
                                    <dd class="mt-1 whitespace-pre-wrap text-slate-900">{{ $loc->notes ?: '—' }}</dd>
                                </div>
                            </dl>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="border-b border-slate-100 pb-2 text-base font-semibold text-slate-900">Contact</h2>
            <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                <div><dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Phone</dt><dd class="mt-1 text-sm text-slate-900">{{ $vendor->phone ?: '—' }}</dd></div>
                <div><dt class="text-xs font-medium uppercase tracking-wide text-slate-500">WhatsApp</dt><dd class="mt-1 text-sm text-slate-900">{{ $vendor->whatsapp ?: '—' }}</dd></div>
                <div><dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Telegram</dt><dd class="mt-1 text-sm text-slate-900">{{ $vendor->telegram ?: '—' }}</dd></div>
                <div><dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Email</dt><dd class="mt-1 text-sm text-slate-900">{{ $vendor->email ?: '—' }}</dd></div>
                <div><dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Website</dt><dd class="mt-1 break-all text-sm text-slate-900">{{ $vendor->website ?: '—' }}</dd></div>
            </dl>
            <div class="mt-6 border-t border-slate-100 pt-4">
                <h3 class="text-sm font-semibold text-slate-900">Social Media</h3>
                <dl class="mt-3 grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Facebook URL</dt>
                        <dd class="mt-1 break-all text-sm text-slate-900">{{ $vendor->facebook_url ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Instagram URL</dt>
                        <dd class="mt-1 break-all text-sm text-slate-900">{{ $vendor->instagram_url ?: '—' }}</dd>
                    </div>
                </dl>
            </div>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="border-b border-slate-100 pb-2 text-base font-semibold text-slate-900">Primary Contact</h2>
            <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                <div><dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Name</dt><dd class="mt-1 text-sm text-slate-900">{{ $vendor->primary_contact_name ?: '—' }}</dd></div>
                <div><dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Position</dt><dd class="mt-1 text-sm text-slate-900">{{ $vendor->primary_contact_position ?: '—' }}</dd></div>
                <div><dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Phone</dt><dd class="mt-1 text-sm text-slate-900">{{ $vendor->primary_contact_phone ?: '—' }}</dd></div>
                <div><dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Email</dt><dd class="mt-1 text-sm text-slate-900">{{ $vendor->primary_contact_email ?: '—' }}</dd></div>
            </dl>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="border-b border-slate-100 pb-2 text-base font-semibold text-slate-900">Secondary Contact</h2>
            <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                <div><dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Name</dt><dd class="mt-1 text-sm text-slate-900">{{ $vendor->secondary_contact_name ?: '—' }}</dd></div>
                <div><dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Position</dt><dd class="mt-1 text-sm text-slate-900">{{ $vendor->secondary_contact_position ?: '—' }}</dd></div>
                <div><dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Phone</dt><dd class="mt-1 text-sm text-slate-900">{{ $vendor->secondary_contact_phone ?: '—' }}</dd></div>
                <div><dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Email</dt><dd class="mt-1 text-sm text-slate-900">{{ $vendor->secondary_contact_email ?: '—' }}</dd></div>
            </dl>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="border-b border-slate-100 pb-2 text-base font-semibold text-slate-900">Procurement</h2>
            <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">RFQ method</dt>
                    <dd class="mt-1 flex flex-wrap gap-2">
                        @if (is_array($vendor->rfq_method) && count($vendor->rfq_method) > 0)
                            @foreach ($vendor->rfq_method as $m)
                                <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-800">{{ \Illuminate\Support\Str::headline(str_replace('_', ' ', (string) $m)) }}</span>
                            @endforeach
                        @else
                            <span class="text-sm text-slate-900">—</span>
                        @endif
                    </dd>
                </div>
                <div><dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Pricing frequency</dt><dd class="mt-1 text-sm text-slate-900">{{ $vendor->pricing_frequency ? $label($vendor->pricing_frequency) : '—' }}</dd></div>
                <div><dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Delivery lead time</dt><dd class="mt-1 text-sm text-slate-900">{{ $vendor->delivery_lead_time instanceof \BackedEnum ? $vendor->delivery_lead_time->label() : ($vendor->delivery_lead_time ? \Illuminate\Support\Str::headline(str_replace('_', ' ', (string) $vendor->delivery_lead_time)) : '—') }}</dd></div>
                <div><dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Execution lead time</dt><dd class="mt-1 text-sm text-slate-900">{{ $vendor->execution_lead_time instanceof \BackedEnum ? $vendor->execution_lead_time->label() : ($vendor->execution_lead_time ? \Illuminate\Support\Str::headline(str_replace('_', ' ', (string) $vendor->execution_lead_time)) : '—') }}</dd></div>
                <div><dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Bulletin price validity (days)</dt><dd class="mt-1 text-sm text-slate-900">{{ $vendor->bulletin_price_validity_days ?? '—' }}</dd></div>
                <div><dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Currency code</dt><dd class="mt-1 text-sm text-slate-900">{{ $vendor->currency_code ? strtoupper($vendor->currency_code) : '—' }}</dd></div>
            </dl>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="border-b border-slate-100 pb-2 text-base font-semibold text-slate-900">Commercial &amp; Technical</h2>
            <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                <div><dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Payment method</dt><dd class="mt-1 text-sm text-slate-900">{{ $vendor->payment_method ? $label($vendor->payment_method) : '—' }}</dd></div>
                <div><dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Rating</dt><dd class="mt-1 text-sm text-slate-900">{{ $vendor->rating !== null ? $vendor->rating : '—' }}</dd></div>
                <div class="sm:col-span-2"><dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Payment terms</dt><dd class="mt-1 whitespace-pre-wrap text-sm text-slate-900">{{ $vendor->payment_terms ?: '—' }}</dd></div>
                <div class="sm:col-span-2"><dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Commercial terms</dt><dd class="mt-1 whitespace-pre-wrap text-sm text-slate-900">{{ $vendor->commercial_terms ?: '—' }}</dd></div>
                <div class="sm:col-span-2"><dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Technical capabilities</dt><dd class="mt-1 whitespace-pre-wrap text-sm text-slate-900">{{ $vendor->technical_capabilities ?: '—' }}</dd></div>
            </dl>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="border-b border-slate-100 pb-2 text-base font-semibold text-slate-900">Classification</h2>
            <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                <div><dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Company type</dt><dd class="mt-1 text-sm text-slate-900">{{ $vendor->company_type ? $label($vendor->company_type) : '—' }}</dd></div>
                <div><dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Coverage</dt><dd class="mt-1 text-sm text-slate-900">{{ $vendor->coverage_type ? $label($vendor->coverage_type) : '—' }}</dd></div>
                <div><dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Tax number</dt><dd class="mt-1 text-sm text-slate-900">{{ $vendor->tax_number ?: '—' }}</dd></div>
                <div><dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Registration number</dt><dd class="mt-1 text-sm text-slate-900">{{ $vendor->registration_number ?: '—' }}</dd></div>
                <div><dt class="text-xs font-medium uppercase tracking-wide text-slate-500">License number</dt><dd class="mt-1 text-sm text-slate-900">{{ $vendor->license_number ?: '—' }}</dd></div>
                <div><dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Has brochures</dt><dd class="mt-1 text-sm text-slate-900">{{ $vendor->has_brochures ? 'Yes' : 'No' }}</dd></div>
            </dl>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="border-b border-slate-100 pb-2 text-base font-semibold text-slate-900">Business types</h2>
            @if ($vendor->businessTypes->isEmpty())
                <p class="mt-3 text-sm text-slate-500">None assigned.</p>
            @else
                <ul class="mt-3 list-inside list-disc text-sm text-slate-900">
                    @foreach ($vendor->businessTypes as $bt)
                        <li>{{ $label($bt->business_type) }}</li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="border-b border-slate-100 pb-2 text-base font-semibold text-slate-900">Categories &amp; subcategories</h2>
            @php
                $groupedCatalog = [];
                foreach ($vendor->vendorCategories->sortBy('id') as $vc) {
                    $cid = (string) $vc->category_id;
                    if (! isset($groupedCatalog[$cid])) {
                        $groupedCatalog[$cid] = [
                            'category' => $vc->category,
                            'subs' => collect(),
                            'is_primary' => false,
                            'category_only' => false,
                        ];
                    }
                    if ($vc->subcategory_id === null) {
                        $groupedCatalog[$cid]['category_only'] = true;
                    } else {
                        $groupedCatalog[$cid]['subs']->push($vc->subcategory);
                    }
                    if ($vc->is_primary) {
                        $groupedCatalog[$cid]['is_primary'] = true;
                    }
                }
            @endphp
            @if (count($groupedCatalog) === 0)
                <p class="mt-3 text-sm text-slate-500">None assigned.</p>
            @else
                <div class="mt-3 space-y-4">
                    @foreach ($groupedCatalog as $group)
                        <div class="rounded-lg border border-slate-200 bg-slate-50/50 p-4">
                            <div class="flex flex-wrap items-center gap-2">
                                @if ($group['is_primary'])
                                    <span class="inline-flex rounded-full bg-slate-900 px-2 py-0.5 text-xs font-medium text-white">Primary</span>
                                @endif
                            </div>
                            <div class="mt-2 text-sm font-medium text-slate-900">
                                @include('procurement.vendors.partials.catalog-label', ['model' => $group['category']])
                            </div>
                            @if ($group['subs']->isEmpty() && $group['category_only'])
                                <p class="mt-2 text-xs text-slate-600">Whole category (no specific subcategories).</p>
                            @elseif ($group['subs']->isNotEmpty())
                                <ul class="mt-2 list-inside list-disc text-sm text-slate-800">
                                    @foreach ($group['subs']->unique('id')->filter() as $sub)
                                        <li>@include('procurement.vendors.partials.catalog-label', ['model' => $sub])</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="border-b border-slate-100 pb-2 text-base font-semibold text-slate-900">Brochures</h2>
            @if ($vendor->brochures->isEmpty())
                <p class="mt-3 text-sm text-slate-500">No brochure files on record.</p>
            @else
                <ul class="mt-3 divide-y divide-slate-100 rounded-lg border border-slate-200 text-sm">
                    @foreach ($vendor->brochures as $brochure)
                        <li class="flex flex-col gap-2 px-3 py-3 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0 flex-1">
                                <div class="font-medium text-slate-900">{{ $brochure->file_name }}</div>
                                <div class="break-all font-mono text-xs text-slate-500">{{ $brochure->url }}</div>
                                @if ($brochure->file_type)
                                    <div class="text-xs text-slate-500">{{ $brochure->file_type }}</div>
                                @endif
                                @if ($brochure->notes)
                                    <div class="mt-1 text-xs text-slate-600"><span class="font-medium text-slate-500">Notes:</span> {{ $brochure->notes }}</div>
                                @endif
                                @if ($brochure->category_id || $brochure->subcategory_id)
                                    <div class="mt-1 text-xs text-slate-600">
                                        <span class="font-medium text-slate-500">Linked:</span>
                                        @if ($brochure->category)
                                            <span dir="auto">{{ $brochure->category->name_ar }}</span>
                                            <span class="text-slate-400"> — </span>
                                            <span class="text-slate-500">{{ $brochure->category->name_en }}</span>
                                        @else
                                            —
                                        @endif
                                        @if ($brochure->subcategory)
                                            <span class="text-slate-400"> / </span>
                                            <span dir="auto">{{ $brochure->subcategory->name_ar }}</span>
                                            <span class="text-slate-400"> — </span>
                                            <span class="text-slate-500">{{ $brochure->subcategory->name_en }}</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            <a href="{{ $brochure->url }}"
                               target="_blank" rel="noopener"
                               class="shrink-0 text-sm font-medium text-slate-700 hover:text-slate-900">Open file</a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
@endsection
