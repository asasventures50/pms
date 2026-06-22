<?php

namespace App\Models\Procurement\Invoices;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    protected $table = 'invoice_items';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'invoice_id',
        'sort_order',
        'line_number',
        'description',
        'quantity',
        'unit',
        'unit_price',
        'line_total',
        'source_purchase_order_item_ids',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'unit_price' => 'decimal:2',
            'line_total' => 'decimal:2',
            'source_purchase_order_item_ids' => 'array',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
