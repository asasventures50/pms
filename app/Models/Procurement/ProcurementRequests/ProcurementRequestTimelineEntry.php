<?php

namespace App\Models\Procurement\ProcurementRequests;

use App\Enums\Procurement\ProcurementRequests\ProcurementTimelineActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcurementRequestTimelineEntry extends Model
{
    protected $table = 'procurement_request_timeline_entries';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'procurement_request_id',
        'activity',
        'duration_days',
    ];

    protected function casts(): array
    {
        return [
            'activity' => ProcurementTimelineActivity::class,
        ];
    }

    public function procurementRequest(): BelongsTo
    {
        return $this->belongsTo(ProcurementRequest::class);
    }
}
