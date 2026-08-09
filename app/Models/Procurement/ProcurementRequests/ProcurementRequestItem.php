<?php

namespace App\Models\Procurement\ProcurementRequests;

use App\Models\Procurement\Projects\Project;
use App\Models\Procurement\Projects\Zone;
use App\Models\Procurement\Rfqs\RfqItem;
use App\Models\Procurement\Vendors\Category;
use App\Models\Procurement\Vendors\Subcategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProcurementRequestItem extends Model
{
    protected $table = 'procurement_request_items';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'procurement_request_id',
        'sort_order',
        'line_number',
        'item_name',
        'project_id',
        'zone_id',
        'category_id',
        'subcategory_id',
        'category',
        'subcategory',
        'scope_type',
        'description',
        'unit',
        'quantity',
        'unit_price',
        'total_price',
        'justification',
        'scope_of_work',
        'required_delivery_date',
        'flexible_delivery_date',
        'delivery_location',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'unit_price' => 'decimal:4',
            'total_price' => 'decimal:4',
            'required_delivery_date' => 'date',
            'flexible_delivery_date' => 'boolean',
        ];
    }

    public function procurementRequest(): BelongsTo
    {
        return $this->belongsTo(ProcurementRequest::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function catalogCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function catalogSubcategory(): BelongsTo
    {
        return $this->belongsTo(Subcategory::class, 'subcategory_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ProcurementRequestDocument::class)->latest();
    }

    public function rfqItems(): HasMany
    {
        return $this->hasMany(RfqItem::class);
    }

    public function resolvedCategoryName(): string
    {
        $this->loadMissing('catalogCategory');

        $fromCatalog = trim((string) ($this->catalogCategory?->name_en ?? $this->catalogCategory?->name_ar ?? ''));
        if ($fromCatalog !== '') {
            return $fromCatalog;
        }

        return trim((string) ($this->attributes['category'] ?? ''));
    }

    public function resolvedSubcategoryName(): string
    {
        $this->loadMissing('catalogSubcategory');

        $fromCatalog = trim((string) ($this->catalogSubcategory?->name_en ?? $this->catalogSubcategory?->name_ar ?? ''));
        if ($fromCatalog !== '') {
            return $fromCatalog;
        }

        return trim((string) ($this->attributes['subcategory'] ?? ''));
    }

    public function resolvedCategoryLabel(): string
    {
        $category = $this->resolvedCategoryName();
        $subcategory = $this->resolvedSubcategoryName();

        return match (true) {
            $category !== '' && $subcategory !== '' => $category.' / '.$subcategory,
            $category !== '' => $category,
            $subcategory !== '' => $subcategory,
            default => '',
        };
    }
}
