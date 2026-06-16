<?php

namespace App\Models\Procurement\VendorQuotations;

use App\Enums\Procurement\VendorQuotations\QuotationCompliance;
use App\Models\Procurement\Rfqs\RfqItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorQuotationItem extends Model
{
    protected $table = 'vendor_quotation_items';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'vendor_quotation_id',
        'rfq_item_id',
        'sort_order',
        'item_number',
        'quantity_quoted',
        'compliance',
        'alternative_if_no',
        'item_description_if_no',
        'brand_origin',
        'brand',
        'model',
        'country_of_origin',
        'unit_price',
        'currency',
        'total_price',
        'discount',
        'tax_rate',
        'tax',
        'delivery_charges',
        'installation',
        'lead_time',
        'warranty',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'compliance' => QuotationCompliance::class,
            'quantity_quoted' => 'decimal:3',
            'unit_price' => 'decimal:2',
            'total_price' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax' => 'decimal:2',
            'delivery_charges' => 'decimal:2',
            'installation' => 'decimal:2',
        ];
    }

    public function vendorQuotation(): BelongsTo
    {
        return $this->belongsTo(VendorQuotation::class);
    }

    public function rfqItem(): BelongsTo
    {
        return $this->belongsTo(RfqItem::class);
    }
}
