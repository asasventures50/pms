<?php

namespace App\Models\Procurement\ScheduleOfWorks;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleOfWorkItem extends Model
{
    protected $table = 'schedule_of_work_items';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'schedule_of_work_id',
        'sort_order',
        'line_number',
        'project_zone',
        'description',
        'quantity',
        'unit',
        'unit_price',
        'line_total',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'unit_price' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function scheduleOfWork(): BelongsTo
    {
        return $this->belongsTo(ScheduleOfWork::class);
    }
}
