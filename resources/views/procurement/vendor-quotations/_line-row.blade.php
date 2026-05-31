@php
    $qty = (float) ($row['quantity'] ?? 0);
@endphp
<tr class="vq-line-row" data-quantity="{{ $qty }}">
    <td class="border border-slate-200 px-2 py-2 font-mono text-xs">{{ $index + 1 }}</td>
    <td class="border border-slate-200 p-0">
        <input type="hidden" name="items[{{ $index }}][rfq_item_id]" value="{{ $row['rfq_item_id'] ?? '' }}">
        <select name="items[{{ $index }}][compliance]" class="vq-compliance admin-filter-control w-full min-w-[8rem] rounded-none border-0">
            <option value="">—</option>
            @foreach ($complianceOptions as $option)
                <option value="{{ $option->value }}" @selected(($row['compliance'] ?? '') === $option->value)>{{ $option->label() }}</option>
            @endforeach
        </select>
    </td>
    <td class="border border-slate-200 p-0">
        <input type="text" name="items[{{ $index }}][alternative_if_no]" value="{{ $row['alternative_if_no'] ?? '' }}"
               class="admin-filter-control w-full min-w-[6rem] rounded-none border-0">
    </td>
    <td class="border border-slate-200 p-0">
        <input type="text" name="items[{{ $index }}][item_description_if_no]" value="{{ $row['item_description_if_no'] ?? '' }}"
               class="admin-filter-control w-full min-w-[8rem] rounded-none border-0">
    </td>
    <td class="border border-slate-200 p-0">
        <input type="text" name="items[{{ $index }}][brand_origin]" value="{{ $row['brand_origin'] ?? '' }}"
               class="admin-filter-control w-full min-w-[6rem] rounded-none border-0">
    </td>
    <td class="border border-slate-200 p-0">
        <input type="number" name="items[{{ $index }}][unit_price]" value="{{ $row['unit_price'] ?? '' }}" min="0" step="0.01"
               class="vq-unit-price admin-filter-control w-full min-w-[5rem] rounded-none border-0">
    </td>
    <td class="border border-slate-200 p-0">
        <input type="text" name="items[{{ $index }}][currency]" value="{{ $row['currency'] ?? '' }}" maxlength="10"
               class="admin-filter-control w-full min-w-[4rem] rounded-none border-0" placeholder="USD">
    </td>
    <td class="border border-slate-200 p-0">
        <input type="number" name="items[{{ $index }}][total_price]" value="{{ $row['total_price'] ?? '' }}" min="0" step="0.01"
               class="vq-total-price admin-filter-control w-full min-w-[5rem] rounded-none border-0">
    </td>
    <td class="border border-slate-200 p-0">
        <input type="number" name="items[{{ $index }}][tax]" value="{{ $row['tax'] ?? '' }}" min="0" step="0.01"
               class="vq-tax admin-filter-control w-full min-w-[4rem] rounded-none border-0">
    </td>
    <td class="border border-slate-200 p-0">
        <input type="text" name="items[{{ $index }}][lead_time]" value="{{ $row['lead_time'] ?? '' }}"
               class="admin-filter-control w-full min-w-[5rem] rounded-none border-0">
    </td>
    <td class="border border-slate-200 p-0">
        <input type="text" name="items[{{ $index }}][warranty]" value="{{ $row['warranty'] ?? '' }}"
               class="admin-filter-control w-full min-w-[5rem] rounded-none border-0">
    </td>
</tr>
