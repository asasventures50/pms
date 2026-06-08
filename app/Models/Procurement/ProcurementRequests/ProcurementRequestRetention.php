<?php

namespace App\Models\Procurement\ProcurementRequests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcurementRequestRetention extends Model
{
    protected $table = 'procurement_request_retentions';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'procurement_request_id',
        'sort_order',
        'retention_percent',
        'release_period',
    ];

    protected function casts(): array
    {
        return [
            'retention_percent' => 'decimal:2',
        ];
    }

    public function procurementRequest(): BelongsTo
    {
        return $this->belongsTo(ProcurementRequest::class);
    }
}
