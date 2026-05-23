@php
    $lineItems = $lineItems ?? [];
    $projects = $projects ?? collect();
@endphp

<section>
    <div>
        <h3 class="text-sm font-bold text-slate-900">Procurement details</h3>
        <p class="mt-1 text-xs text-slate-500">Add one card per item. Use <span class="font-medium">Add line</span> at the bottom to add another.</p>
    </div>

    @error('items')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror

    <div id="pr-lines-body" class="mt-4 space-y-4">
        @foreach ($lineItems as $index => $row)
            @include('procurement.procurement-requests._line-item-card', [
                'index' => $index,
                'row' => $row,
                'projects' => $projects,
            ])
        @endforeach
    </div>

    @include('procurement.partials._add-line-button', ['id' => 'pr-add-line'])

    <template id="pr-line-template">
        @include('procurement.procurement-requests._line-item-card', [
            'index' => 0,
            'projects' => $projects,
            'row' => [
                'project_id' => '',
                'zone_id' => '',
                'category' => '',
                'subcategory' => '',
                'scope_type' => [],
                'description' => '',
                'unit' => '',
                'quantity' => 1,
                'justification' => '',
                'required_delivery_date' => '',
                'flexible_delivery_date' => true,
                'delivery_location' => '',
            ],
        ])
    </template>
</section>

@if (auth()->user()->hasPermission('projects.create'))
    @include('procurement.procurement-requests.partials._quick-add-project-modal')
@endif
@if (auth()->user()->hasPermission('projects.update'))
    @include('procurement.procurement-requests.partials._quick-add-zone-modal')
@endif
