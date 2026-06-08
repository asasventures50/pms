@php
    $index = $index ?? 0;
    $row = $row ?? [];
    $rowId = old("payment_terms.$index.id", $row['id'] ?? '');
@endphp

<tr class="pr-payment-term-row">
    @if ($rowId !== '' && $rowId !== null)
        <td class="hidden"><input type="hidden" name="payment_terms[{{ $index }}][id]" value="{{ $rowId }}" data-name="id"></td>
    @endif
    <td class="px-2 py-2"><input type="text" name="payment_terms[{{ $index }}][milestone]" value="{{ old("payment_terms.$index.milestone", $row['milestone'] ?? '') }}" data-name="milestone" class="admin-filter-control w-full min-w-[8rem]"></td>
    <td class="px-2 py-2"><input type="text" name="payment_terms[{{ $index }}][amount]" value="{{ old("payment_terms.$index.amount", $row['amount'] ?? '') }}" data-name="amount" class="admin-filter-control w-full min-w-[8rem]"></td>
    <td class="px-2 py-2"><input type="number" name="payment_terms[{{ $index }}][percentage]" value="{{ old("payment_terms.$index.percentage", $row['percentage'] ?? '') }}" data-name="percentage" min="0" max="100" step="0.01" class="admin-filter-control w-24"></td>
    <td class="px-2 py-2"><input type="text" name="payment_terms[{{ $index }}][due_upon]" value="{{ old("payment_terms.$index.due_upon", $row['due_upon'] ?? '') }}" data-name="due_upon" class="admin-filter-control w-full min-w-[10rem]"></td>
    <td class="px-2 py-2 print:hidden"><button type="button" class="pr-remove-payment-term text-sm font-medium text-red-700">Remove</button></td>
</tr>
