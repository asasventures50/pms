<?php

namespace App\Services\Procurement\Categories;

use App\Models\Procurement\ProcurementRequests\ProcurementRequestItem;
use App\Models\Procurement\QuickReceipts\QuickReceipt;
use App\Models\Procurement\Vendors\Category;
use App\Models\Procurement\Vendors\Subcategory;
use App\Models\Procurement\Vendors\VendorBrochure;
use App\Models\Procurement\Vendors\VendorCategory;
use App\Support\Procurement\Categories\CategoryNameSimilarity;
use Illuminate\Support\Collection;

class CategoryRebuildPreviewBuilder
{
    public const SUGGEST_THRESHOLD = 55;

    public function __construct(
        private CategoryNameSimilarity $similarity,
    ) {}

    /**
     * @param  list<array{key: string, name_ar: string, name_en: string, slug: string, subcategories: list<array{key: string, name_ar: string, name_en: string, slug: string}>}>  $proposed
     * @return array{
     *     proposed: list<array<string, mixed>>,
     *     current: list<array<string, mixed>>,
     *     totals: array<string, int>
     * }
     */
    public function build(array $proposed): array
    {
        $impacts = $this->loadImpacts();
        $existingNameEns = Category::query()->pluck('name_en')->map(fn ($name) => mb_strtolower((string) $name))->all();

        $proposedWithFlags = [];
        foreach ($proposed as $category) {
            $exists = in_array(mb_strtolower($category['name_en']), $existingNameEns, true);
            $proposedWithFlags[] = array_merge($category, [
                'already_exists' => $exists,
                'subcategory_count' => count($category['subcategories']),
            ]);
        }

        $current = [];
        $categories = Category::query()
            ->with(['subcategories' => fn ($q) => $q->orderBy('name_ar')->orderBy('name_en')->orderBy('id')])
            ->orderBy('name_ar')
            ->orderBy('name_en')
            ->orderBy('id')
            ->get();

        foreach ($categories as $category) {
            $suggested = $this->bestCategoryMatch($category, $proposed);
            $subs = [];
            foreach ($category->subcategories as $subcategory) {
                $subSuggested = $this->bestSubcategoryMatch($subcategory, $proposed, $suggested['key'] ?? null);
                $subs[] = [
                    'id' => $subcategory->id,
                    'name_ar' => $subcategory->name_ar,
                    'name_en' => $subcategory->name_en,
                    'slug' => $subcategory->slug,
                    'suggested_key' => $subSuggested['key'],
                    'suggestion_score' => $subSuggested['score'],
                    'impact' => [
                        'procurement_requests' => (int) ($impacts['pr_sub'][$subcategory->id] ?? 0),
                        'vendor_links' => (int) ($impacts['vendor_sub'][$subcategory->id] ?? 0),
                        'brochures' => (int) ($impacts['brochure_sub'][$subcategory->id] ?? 0),
                    ],
                ];
            }

            $current[] = [
                'id' => $category->id,
                'name_ar' => $category->name_ar,
                'name_en' => $category->name_en,
                'slug' => $category->slug,
                'suggested_key' => $suggested['key'],
                'suggestion_score' => $suggested['score'],
                'impact' => [
                    'procurement_requests' => (int) ($impacts['pr_cat'][$category->id] ?? 0),
                    'vendor_links' => (int) ($impacts['vendor_cat'][$category->id] ?? 0),
                    'brochures' => (int) ($impacts['brochure_cat'][$category->id] ?? 0),
                    'quick_receipts' => (int) ($impacts['qr_cat'][$category->id] ?? 0),
                ],
                'subcategories' => $subs,
            ];
        }

        return [
            'proposed' => $proposedWithFlags,
            'current' => $current,
            'totals' => $this->totals($proposedWithFlags, $current),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $proposed
     * @return array{key: string|null, score: int}
     */
    private function bestCategoryMatch(Category $category, array $proposed): array
    {
        $bestKey = null;
        $bestScore = 0;
        $left = [
            'name_ar' => (string) $category->name_ar,
            'name_en' => (string) $category->name_en,
            'slug' => (string) $category->slug,
        ];

        foreach ($proposed as $candidate) {
            $score = $this->similarity->score($left, $candidate);
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestKey = $candidate['key'];
            }
        }

        if ($bestScore < self::SUGGEST_THRESHOLD) {
            return ['key' => null, 'score' => $bestScore];
        }

        return ['key' => $bestKey, 'score' => $bestScore];
    }

    /**
     * @param  list<array<string, mixed>>  $proposed
     * @return array{key: string|null, score: int}
     */
    private function bestSubcategoryMatch(
        Subcategory $subcategory,
        array $proposed,
        ?string $suggestedCategoryKey,
    ): array {
        $left = [
            'name_ar' => (string) $subcategory->name_ar,
            'name_en' => (string) $subcategory->name_en,
            'slug' => (string) $subcategory->slug,
        ];

        $bestKey = null;
        $bestScore = 0;

        foreach ($proposed as $proposedCategory) {
            $parentBoost = $suggestedCategoryKey !== null && $proposedCategory['key'] === $suggestedCategoryKey
                ? 12
                : 0;

            foreach ($proposedCategory['subcategories'] as $candidate) {
                $score = $this->similarity->score($left, $candidate) + $parentBoost;
                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestKey = $candidate['key'];
                }
            }
        }

        $rawScore = min(100, $bestScore);
        if ($rawScore < self::SUGGEST_THRESHOLD) {
            return ['key' => null, 'score' => $rawScore];
        }

        return ['key' => $bestKey, 'score' => $rawScore];
    }

    /**
     * @return array{
     *     pr_sub: Collection<int, int>,
     *     pr_cat: Collection<int, int>,
     *     vendor_sub: Collection<int, int>,
     *     vendor_cat: Collection<int, int>,
     *     brochure_sub: Collection<int, int>,
     *     brochure_cat: Collection<int, int>,
     *     qr_cat: Collection<int, int>
     * }
     */
    private function loadImpacts(): array
    {
        return [
            'pr_sub' => ProcurementRequestItem::query()
                ->whereNotNull('subcategory_id')
                ->selectRaw('subcategory_id, count(*) as c')
                ->groupBy('subcategory_id')
                ->pluck('c', 'subcategory_id'),
            'pr_cat' => ProcurementRequestItem::query()
                ->whereNotNull('category_id')
                ->whereNull('subcategory_id')
                ->selectRaw('category_id, count(*) as c')
                ->groupBy('category_id')
                ->pluck('c', 'category_id'),
            'vendor_sub' => VendorCategory::query()
                ->whereNotNull('subcategory_id')
                ->selectRaw('subcategory_id, count(*) as c')
                ->groupBy('subcategory_id')
                ->pluck('c', 'subcategory_id'),
            'vendor_cat' => VendorCategory::query()
                ->whereNull('subcategory_id')
                ->selectRaw('category_id, count(*) as c')
                ->groupBy('category_id')
                ->pluck('c', 'category_id'),
            'brochure_sub' => VendorBrochure::query()
                ->whereNotNull('subcategory_id')
                ->selectRaw('subcategory_id, count(*) as c')
                ->groupBy('subcategory_id')
                ->pluck('c', 'subcategory_id'),
            'brochure_cat' => VendorBrochure::query()
                ->whereNotNull('category_id')
                ->whereNull('subcategory_id')
                ->selectRaw('category_id, count(*) as c')
                ->groupBy('category_id')
                ->pluck('c', 'category_id'),
            'qr_cat' => QuickReceipt::query()
                ->whereNotNull('category_id')
                ->selectRaw('category_id, count(*) as c')
                ->groupBy('category_id')
                ->pluck('c', 'category_id'),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $proposed
     * @param  list<array<string, mixed>>  $current
     * @return array<string, int>
     */
    private function totals(array $proposed, array $current): array
    {
        $pr = 0;
        $vendors = 0;
        $brochures = 0;
        $receipts = 0;
        $suggested = 0;
        $withUsageUnmapped = 0;

        foreach ($current as $category) {
            $catImpact = (int) $category['impact']['procurement_requests']
                + (int) $category['impact']['vendor_links']
                + (int) $category['impact']['brochures']
                + (int) $category['impact']['quick_receipts'];
            $pr += (int) $category['impact']['procurement_requests'];
            $vendors += (int) $category['impact']['vendor_links'];
            $brochures += (int) $category['impact']['brochures'];
            $receipts += (int) $category['impact']['quick_receipts'];

            if ($category['suggested_key']) {
                $suggested++;
            } elseif ($catImpact > 0) {
                $withUsageUnmapped++;
            }

            foreach ($category['subcategories'] as $sub) {
                $subImpact = (int) $sub['impact']['procurement_requests']
                    + (int) $sub['impact']['vendor_links']
                    + (int) $sub['impact']['brochures'];
                $pr += (int) $sub['impact']['procurement_requests'];
                $vendors += (int) $sub['impact']['vendor_links'];
                $brochures += (int) $sub['impact']['brochures'];
                if ($sub['suggested_key']) {
                    $suggested++;
                } elseif ($subImpact > 0) {
                    $withUsageUnmapped++;
                }
            }
        }

        return [
            'proposed_categories' => count($proposed),
            'proposed_subcategories' => array_sum(array_map(fn ($c) => count($c['subcategories']), $proposed)),
            'proposed_new_categories' => count(array_filter($proposed, fn ($c) => empty($c['already_exists']))),
            'current_categories' => count($current),
            'current_subcategories' => array_sum(array_map(fn ($c) => count($c['subcategories']), $current)),
            'suggested_mappings' => $suggested,
            'used_without_suggestion' => $withUsageUnmapped,
            'procurement_requests' => $pr,
            'vendor_links' => $vendors,
            'brochures' => $brochures,
            'quick_receipts' => $receipts,
        ];
    }
}
