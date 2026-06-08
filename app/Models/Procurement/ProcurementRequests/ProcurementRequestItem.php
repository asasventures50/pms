<?php

namespace App\Models\Procurement\ProcurementRequests;

use App\Models\Procurement\Projects\Project;
use App\Models\Procurement\Projects\Zone;
use Illuminate\Database\Eloquent\Model;
use App\Models\Procurement\Rfqs\RfqItem;
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

    public function documents(): HasMany
    {
        return $this->hasMany(ProcurementRequestDocument::class)->latest();
    }

    public function rfqItems(): HasMany
    {
        return $this->hasMany(RfqItem::class);
    }
}
