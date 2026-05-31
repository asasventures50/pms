<?php

namespace App\Models\Procurement\Rfqs;

use App\Models\Procurement\ProcurementRequests\ProcurementRequestItem;
use App\Models\Procurement\VendorQuotations\VendorQuotationItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RfqItem extends Model
{
    protected $table = 'rfq_items';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'rfq_id',
        'procurement_request_item_id',
        'sort_order',
        'item',
        'description',
        'quantity',
        'unit',
        'request_lead_time',
        'compliance',
        'unit_price',
        'line_total',
        'quote_lead_time',
        'warranty',
        'line_terms',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'unit_price' => 'decimal:2',
            'line_total' => 'decimal:2',
            'line_terms' => 'array',
        ];
    }

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(Rfq::class);
    }

    public function procurementRequestItem(): BelongsTo
    {
        return $this->belongsTo(ProcurementRequestItem::class);
    }

    public function vendorQuotationItems(): HasMany
    {
        return $this->hasMany(VendorQuotationItem::class);
    }
}
