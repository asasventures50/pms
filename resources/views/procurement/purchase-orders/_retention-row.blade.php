@php
    $row = $row ?? ['retention_percent' => '', 'release_period' => ''];
@endphp

<tr class="po-retention-row">
    <td class="px-2 py-2">
        <input type="number" name="retentions[{{ $index }}][retention_percent]"
               value="{{ old("retentions.$index.retention_percent", $row['retention_percent'] ?? '') }}"
               data-name="retention_percent" min="0" max="100" step="0.01"
               class="admin-filter-control w-32">
    </td>
    <td class="px-2 py-2">
        <input type="text" name="retentions[{{ $index }}][release_period]"
               value="{{ old("retentions.$index.release_period", $row['release_period'] ?? '') }}"
               data-name="release_period"
               class="admin-filter-control w-full min-w-[10rem]">
    </td>
    <td class="px-2 py-2 print:hidden">
        <button type="button" class="po-remove-retention text-sm text-red-600 hover:text-red-800">Remove</button>
    </td>
</tr>
