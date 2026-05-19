<?php

namespace App\Models\Procurement\ProcurementRequests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcurementRequestItem extends Model
{
    protected $table = 'procurement_request_items';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'procurement_request_id',
        'sort_order',
        'zone',
        'category',
        'subcategory',
        'scope_type',
        'description',
        'unit',
        'quantity',
        'justification',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
        ];
    }

    public function procurementRequest(): BelongsTo
    {
        return $this->belongsTo(ProcurementRequest::class);
    }
}
