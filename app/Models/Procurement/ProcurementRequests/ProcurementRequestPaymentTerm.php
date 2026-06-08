<?php

namespace App\Models\Procurement\ProcurementRequests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcurementRequestPaymentTerm extends Model
{
    protected $table = 'procurement_request_payment_terms';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'procurement_request_id',
        'sort_order',
        'milestone',
        'amount',
        'percentage',
        'due_upon',
    ];

    protected function casts(): array
    {
        return [
            'percentage' => 'decimal:2',
        ];
    }

    public function procurementRequest(): BelongsTo
    {
        return $this->belongsTo(ProcurementRequest::class);
    }
}
