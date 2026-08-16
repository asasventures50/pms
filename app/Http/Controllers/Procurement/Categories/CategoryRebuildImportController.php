<?php

namespace App\Http\Controllers\Procurement\Categories;

use App\Http\Controllers\Controller;
use App\Http\Requests\Procurement\Categories\ApplyCategoryRebuildRequest;
use App\Http\Requests\Procurement\Categories\ImportCategoriesRequest;
use App\Services\Procurement\Categories\CategoryRebuildApplyService;
use App\Services\Procurement\Categories\CategoryRebuildPreviewBuilder;
use App\Services\Procurement\Categories\CategoryWorkbookParser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryRebuildImportController extends Controller
{
    public const SESSION_KEY = 'category_rebuild';

    public function __construct(
        private CategoryWorkbookParser $parser,
        private CategoryRebuildPreviewBuilder $previewBuilder,
        private CategoryRebuildApplyService $applyService,
    ) {}

    public function preview(ImportCategoriesRequest $request): RedirectResponse
    {
        $parsed = $this->parser->parse($request->file('file'));

        $request->session()->put(self::SESSION_KEY, [
            'filename' => $request->file('file')->getClientOriginalName(),
            'sheet' => $parsed['sheet'],
            'categories' => $parsed['categories'],
        ]);

        return redirect()->route('categories.import.rebuild');
    }

    public function show(Request $request): View|RedirectResponse
    {
        $payload = $request->session()->get(self::SESSION_KEY);
        if (! is_array($payload) || empty($payload['categories'])) {
            return redirect()
                ->route('categories.import.form')
                ->with('error', 'Upload a catalog file first to preview mappings.');
        }

        $preview = $this->previewBuilder->build($payload['categories']);

        return view('procurement.categories.rebuild-preview', [
            'filename' => $payload['filename'] ?? 'uploaded file',
            'sheet' => $payload['sheet'] ?? '',
            'proposed' => $preview['proposed'],
            'current' => $preview['current'],
            'totals' => $preview['totals'],
        ]);
    }

    public function apply(ApplyCategoryRebuildRequest $request): RedirectResponse
    {
        $payload = $request->session()->get(self::SESSION_KEY);
        if (! is_array($payload) || empty($payload['categories'])) {
            return redirect()
                ->route('categories.import.form')
                ->with('error', 'The mapping session expired. Upload the file again.');
        }

        $result = $this->applyService->apply(
            $payload['categories'],
            $request->input('category_map', []),
            $request->input('subcategory_map', []),
            $request->boolean('retire_mapped'),
        );

        $request->session()->forget(self::SESSION_KEY);

        return redirect()
            ->route('categories.index')
            ->with('success', $result->summaryLine());
    }

    public function cancel(Request $request): RedirectResponse
    {
        $request->session()->forget(self::SESSION_KEY);

        return redirect()
            ->route('categories.import.form')
            ->with('success', 'Mapping preview discarded.');
    }
}
