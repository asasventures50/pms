<div id="sow-pr-import-modal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true" aria-labelledby="sow-pr-import-title">
    <div class="absolute inset-0 bg-slate-900/50" data-sow-pr-import-dismiss></div>
    <div class="relative mx-auto mt-16 flex max-h-[calc(100vh-5rem)] w-full max-w-4xl flex-col rounded-xl border border-slate-200 bg-white shadow-xl">
        <div class="border-b border-slate-200 px-6 py-4">
            <h3 id="sow-pr-import-title" class="text-lg font-semibold text-slate-900">Import lines from P.R.</h3>
            <p id="sow-pr-import-subtitle" class="mt-1 text-sm text-slate-500"></p>
        </div>
        <div class="min-h-0 flex-1 overflow-auto px-6 py-4">
            <p id="sow-pr-import-empty" class="hidden py-6 text-center text-sm text-slate-500">This P.R. has no line items.</p>
            <table id="sow-pr-import-table" class="min-w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-3 py-2 w-10">
                        <input type="checkbox" id="sow-pr-import-select-all" class="rounded border-slate-300" title="Select all">
                    </th>
                    <th class="px-3 py-2">Line</th>
                    <th class="px-3 py-2">Project / zone</th>
                    <th class="px-3 py-2">Description</th>
                    <th class="px-3 py-2 text-right">Qty</th>
                    <th class="px-3 py-2">Unit</th>
                    <th class="px-3 py-2 text-right">Unit price</th>
                </tr>
                </thead>
                <tbody id="sow-pr-import-body" class="divide-y divide-slate-100"></tbody>
            </table>
        </div>
        <div class="flex flex-wrap justify-end gap-3 border-t border-slate-200 px-6 py-4">
            <button type="button" data-sow-pr-import-dismiss
                    class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">
                Cancel
            </button>
            <button type="button" id="sow-pr-import-confirm"
                    class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-50"
                    disabled>
                Import selected
            </button>
        </div>
    </div>
</div>
