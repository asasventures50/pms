@php
    $qty = (float) ($row['quantity'] ?? 0);
    $qtyQuoted = $row['quantity_quoted'] ?? $qty;
@endphp
<tr class="vq-line-row" data-quantity="{{ $qty }}">
    <td class="border border-slate-900 px-1 py-1 font-mono text-xs align-top">{{ $index + 1 }}</td>
    <td class="border border-slate-900 p-0 align-top">
        <input type="hidden" name="items[{{ $index }}][rfq_item_id]" value="{{ $row['rfq_item_id'] ?? '' }}">
        <select name="items[{{ $index }}][compliance]" class="vq-compliance admin-filter-control w-full min-w-[7rem] rounded-none border-0 text-xs">
            <option value="">—</option>
            @foreach ($complianceOptions as $option)
                <option value="{{ $option->value }}" @selected(($row['compliance'] ?? '') === $option->value)>{{ $option->label() }}</option>
            @endforeach
        </select>
    </td>
    <td class="border border-slate-900 p-0 align-top">
        <textarea name="items[{{ $index }}][alternative_if_no]" rows="2"
                  class="admin-form-textarea w-full min-w-[6rem] rounded-none border-0 text-xs">{{ $row['alternative_if_no'] ?? '' }}</textarea>
    </td>
    <td class="border border-slate-900 p-0 align-top">
        <textarea name="items[{{ $index }}][item_description_if_no]" rows="2"
                  class="admin-form-textarea w-full min-w-[7rem] rounded-none border-0 text-xs">{{ $row['item_description_if_no'] ?? '' }}</textarea>
    </td>
    <td class="border border-slate-900 p-0 align-top">
        <input type="text" name="items[{{ $index }}][brand]" value="{{ $row['brand'] ?? '' }}"
               class="admin-filter-control w-full min-w-[5rem] rounded-none border-0 text-xs" placeholder="Brand">
        <input type="text" name="items[{{ $index }}][model]" value="{{ $row['model'] ?? '' }}"
               class="admin-filter-control mt-1 w-full min-w-[5rem] rounded-none border-0 text-xs" placeholder="Model">
    </td>
    <td class="border border-slate-900 p-0 align-top">
        <input type="text" name="items[{{ $index }}][country_of_origin]" value="{{ $row['country_of_origin'] ?? '' }}"
               class="admin-filter-control w-full min-w-[5rem] rounded-none border-0 text-xs">
    </td>
    <td class="border border-slate-900 p-0 align-top">
        <input type="number" name="items[{{ $index }}][unit_price]" value="{{ $row['unit_price'] ?? '' }}" min="0" step="0.01"
               class="vq-unit-price admin-filter-control w-full min-w-[4.5rem] rounded-none border-0 text-xs">
    </td>
    <td class="border border-slate-900 p-0 align-top">
        <input type="text" name="items[{{ $index }}][currency]" value="{{ $row['currency'] ?? '' }}" maxlength="10"
               class="admin-filter-control w-full min-w-[3.5rem] rounded-none border-0 text-xs" placeholder="SAR">
    </td>
    <td class="border border-slate-900 p-0 align-top">
        <input type="number" name="items[{{ $index }}][quantity_quoted]" value="{{ $qtyQuoted }}" min="0" step="0.001"
               class="vq-qty-quoted admin-filter-control w-full min-w-[4rem] rounded-none border-0 text-xs">
    </td>
    <td class="border border-slate-900 p-0 align-top">
        <input type="number" name="items[{{ $index }}][total_price]" value="{{ $row['total_price'] ?? '' }}" min="0" step="0.01"
               class="vq-total-price admin-filter-control w-full min-w-[4.5rem] rounded-none border-0 text-xs">
    </td>
    <td class="border border-slate-900 p-0 align-top">
        <input type="number" name="items[{{ $index }}][discount]" value="{{ $row['discount'] ?? '' }}" min="0" step="0.01"
               class="vq-discount admin-filter-control w-full min-w-[3.5rem] rounded-none border-0 text-xs">
    </td>
    <td class="border border-slate-900 p-0 align-top">
        <input type="number" name="items[{{ $index }}][tax_rate]" value="{{ $row['tax_rate'] ?? '' }}" min="0" max="100" step="0.01"
               class="vq-tax-rate admin-filter-control w-full min-w-[3rem] rounded-none border-0 text-xs" placeholder="%">
        <input type="number" name="items[{{ $index }}][tax]" value="{{ $row['tax'] ?? '' }}" min="0" step="0.01"
               class="vq-tax admin-filter-control mt-1 w-full min-w-[3.5rem] rounded-none border-0 text-xs">
    </td>
    <td class="border border-slate-900 p-0 align-top">
        <input type="number" name="items[{{ $index }}][delivery_charges]" value="{{ $row['delivery_charges'] ?? '' }}" min="0" step="0.01"
               class="vq-line-delivery admin-filter-control w-full min-w-[3.5rem] rounded-none border-0 text-xs">
    </td>
    <td class="border border-slate-900 p-0 align-top">
        <input type="number" name="items[{{ $index }}][installation]" value="{{ $row['installation'] ?? '' }}" min="0" step="0.01"
               class="vq-line-installation admin-filter-control w-full min-w-[3.5rem] rounded-none border-0 text-xs">
    </td>
    <td class="border border-slate-900 px-1 py-1 text-right font-mono text-xs align-top">
        <span class="vq-line-total">0.00</span>
    </td>
    <td class="border border-slate-900 p-0 align-top">
        <input type="text" name="items[{{ $index }}][lead_time]" value="{{ $row['lead_time'] ?? '' }}"
               class="admin-filter-control w-full min-w-[4rem] rounded-none border-0 text-xs">
    </td>
    <td class="border border-slate-900 p-0 align-top">
        <input type="text" name="items[{{ $index }}][warranty]" value="{{ $row['warranty'] ?? '' }}"
               class="admin-filter-control w-full min-w-[4rem] rounded-none border-0 text-xs">
    </td>
    <td class="border border-slate-900 p-0 align-top">
        <textarea name="items[{{ $index }}][remarks]" rows="2"
                  class="admin-form-textarea w-full min-w-[5rem] rounded-none border-0 text-xs">{{ $row['remarks'] ?? '' }}</textarea>
    </td>
</tr>
