@php
    $index = $index ?? 0;
    $row = $row ?? [];
    $rowId = old("retentions.$index.id", $row['id'] ?? '');
@endphp

<tr class="pr-retention-row">
    @if ($rowId !== '' && $rowId !== null)
        <td class="hidden"><input type="hidden" name="retentions[{{ $index }}][id]" value="{{ $rowId }}" data-name="id"></td>
    @endif
    <td class="px-2 py-2"><input type="number" name="retentions[{{ $index }}][retention_percent]" value="{{ old("retentions.$index.retention_percent", $row['retention_percent'] ?? '') }}" data-name="retention_percent" min="0" max="100" step="0.01" class="admin-filter-control w-32"></td>
    <td class="px-2 py-2"><input type="text" name="retentions[{{ $index }}][release_period]" value="{{ old("retentions.$index.release_period", $row['release_period'] ?? '') }}" data-name="release_period" class="admin-filter-control w-full min-w-[12rem]"></td>
    <td class="px-2 py-2 print:hidden"><button type="button" class="pr-remove-retention text-sm font-medium text-red-700">Remove</button></td>
</tr>
