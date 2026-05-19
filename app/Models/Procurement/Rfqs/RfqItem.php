<?php

namespace App\Models\Procurement\Rfqs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RfqItem extends Model
{
    protected $table = 'rfq_items';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'rfq_id',
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
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'unit_price' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(Rfq::class);
    }
}
