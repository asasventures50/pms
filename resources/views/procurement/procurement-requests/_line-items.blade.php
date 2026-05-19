@php
    $lineItems = $lineItems ?? [];
@endphp

<section>
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h3 class="text-sm font-bold text-slate-900">Procurement details</h3>
            <p class="mt-1 text-xs text-slate-500">Add one card per item. Required: item description.</p>
        </div>
        <button type="button" id="pr-add-line"
                class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50 print:hidden">
            Add line
        </button>
    </div>

    @error('items')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror

    <div id="pr-lines-body" class="mt-4 space-y-4">
        @foreach ($lineItems as $index => $row)
            @include('procurement.procurement-requests._line-item-card', ['index' => $index, 'row' => $row])
        @endforeach
    </div>

    <template id="pr-line-template">
        @include('procurement.procurement-requests._line-item-card', ['index' => 0, 'row' => [
            'zone' => '',
            'category' => '',
            'subcategory' => '',
            'scope_type' => '',
            'description' => '',
            'unit' => '',
            'quantity' => 1,
            'justification' => '',
        ]])
    </template>
</section>
