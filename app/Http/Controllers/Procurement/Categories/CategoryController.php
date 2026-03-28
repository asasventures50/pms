<?php

namespace App\Http\Controllers\Procurement\Categories;

use App\Exports\Procurement\CategoriesExport;
use App\Exports\Procurement\CategoriesTemplateExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Procurement\Categories\ImportCategoriesRequest;
use App\Http\Requests\Procurement\Categories\StoreCategoryRequest;
use App\Http\Requests\Procurement\Categories\UpdateCategoryRequest;
use App\Imports\Procurement\CategoriesImport;
use App\Models\Procurement\Vendors\Category;
use App\Services\Procurement\Categories\CategoryCatalogService;
use App\Support\TableSort;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CategoryController extends Controller
{
    public function __construct(
        protected CategoryCatalogService $catalogService
    ) {}

    public function index(Request $request): View
    {
        $perPage = max(1, min(100, (int) $request->query('per_page', 15)));

        $allowedSorts = ['name_ar', 'name_en', 'created_at'];
        $sort = TableSort::resolve($request, $allowedSorts, 'name_ar', 'asc');

        $query = Category::query()
            ->withCount('subcategories');

        if ($request->filled('q')) {
            $term = '%'.$request->string('q').'%';
            $query->where(function ($q) use ($term) {
                $q->where('name_ar', 'like', $term)
                    ->orWhere('name_en', 'like', $term)
                    ->orWhere('slug', 'like', $term);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $query->orderBy($sort['column'], $sort['direction'])
            ->orderBy('id');

        $categories = $query->paginate($perPage)->appends($request->query());

        return view('procurement.categories.index', [
            'categories' => $categories,
            'sortColumn' => $sort['column'],
            'sortDirection' => $sort['direction'],
        ]);
    }

    public function create(): View
    {
        return view('procurement.categories.create', [
            'category' => new Category([
                'status' => 'active',
            ]),
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $category = Category::query()->create([
                'name_en' => $data['name_en'],
                'name_ar' => $data['name_ar'],
                'slug' => $data['slug'],
                'status' => $data['status'],
            ]);

            $this->catalogService->syncSubcategories($category, $data['subcategories'] ?? []);
        });

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function show(Category $category): View
    {
        $category->load(['subcategories' => fn ($q) => $q->orderBy('name_en')]);

        return view('procurement.categories.show', [
            'category' => $category,
        ]);
    }

    public function edit(Category $category): View
    {
        $category->load(['subcategories' => fn ($q) => $q->orderBy('name_en')]);

        return view('procurement.categories.edit', [
            'category' => $category,
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $category) {
            $category->update([
                'name_en' => $data['name_en'],
                'name_ar' => $data['name_ar'],
                'slug' => $data['slug'],
                'status' => $data['status'],
            ]);

            $this->catalogService->syncSubcategories($category, $data['subcategories'] ?? []);
        });

        return redirect()
            ->route('categories.show', $category)
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->catalogService->softDeleteCategoryCascade($category);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category deleted successfully.');
    }

    public function export(): BinaryFileResponse
    {
        $filename = 'categories-export-'.now()->format('Y-m-d_His').'.xlsx';

        return Excel::download(new CategoriesExport, $filename);
    }

    public function importForm(): View
    {
        return view('procurement.categories.import');
    }

    public function import(ImportCategoriesRequest $request): RedirectResponse
    {
        /** @var CategoriesImport $import */
        $import = app(CategoriesImport::class);

        Excel::import($import, $request->file('file'));

        $result = $import->result;

        $message = $result->summaryLine();

        if ($result->failedRows > 0 || count($result->errors) > 0) {
            $detail = count($result->errors) > 10
                ? array_merge(array_slice($result->errors, 0, 10), ['Additional errors omitted.'])
                : $result->errors;

            return redirect()
                ->route('categories.import.form')
                ->with('error', $message)
                ->with('import_errors', $detail);
        }

        return redirect()
            ->route('categories.index')
            ->with('success', $message);
    }

    public function downloadTemplate(): BinaryFileResponse
    {
        return Excel::download(new CategoriesTemplateExport, 'categories-import-template.xlsx');
    }
}
