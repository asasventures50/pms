@php
    $index = $index ?? 0;
    $row = $row ?? [];
    $itemId = old("items.$index.id", $row['id'] ?? '');
@endphp

<tr class="pr-boq-row">
    <td class="px-2 py-2 align-top">
        @if ($itemId !== '' && $itemId !== null)
            <input type="hidden" name="items[{{ $index }}][id]" value="{{ $itemId }}" data-name="id">
        @endif
        <input type="text" name="items[{{ $index }}][item_name]" value="{{ old("items.$index.item_name", $row['item_name'] ?? '') }}"
               data-name="item_name"
               class="admin-filter-control w-full min-w-[7rem]">
    </td>
    <td class="px-2 py-2 align-top">
        <textarea name="items[{{ $index }}][description]" rows="3" data-name="description" required
                  class="admin-filter-control w-full min-w-[12rem] resize-y @error("items.$index.description") border-red-500 @enderror">{{ old("items.$index.description", $row['description'] ?? '') }}</textarea>
        @error("items.$index.description")<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </td>
    <td class="px-2 py-2 align-top">
        <input type="number" name="items[{{ $index }}][quantity]" value="{{ old("items.$index.quantity", $row['quantity'] ?? 1) }}"
               min="0" step="0.001" data-name="quantity" data-pr-boq-qty required
               class="admin-filter-control w-24">
    </td>
    <td class="px-2 py-2 align-top">
        <input type="text" name="items[{{ $index }}][unit]" value="{{ old("items.$index.unit", $row['unit'] ?? '') }}"
               data-name="unit"
               class="admin-filter-control w-20">
    </td>
    <td class="px-2 py-2 align-top">
        <input type="number" name="items[{{ $index }}][unit_price]" value="{{ old("items.$index.unit_price", $row['unit_price'] ?? 0) }}"
               min="0" step="0.0001" data-name="unit_price" data-pr-boq-unit-price
               class="admin-filter-control w-28">
    </td>
    <td class="px-2 py-2 align-top">
        <input type="number" name="items[{{ $index }}][total_price]" value="{{ old("items.$index.total_price", $row['total_price'] ?? 0) }}"
               min="0" step="0.0001" data-name="total_price" data-pr-boq-total readonly
               class="admin-filter-control w-28 bg-slate-50">
    </td>
    <td class="px-2 py-2 align-top print:hidden">
        <button type="button" class="pr-remove-boq-row text-sm font-medium text-red-700 hover:text-red-900">Remove</button>
    </td>
</tr>
