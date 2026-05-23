<?php

namespace App\Models\Procurement\ProcurementRequests;

use App\Enums\Procurement\ProcurementRequests\ProcurementRequestStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProcurementRequest extends Model
{
    use SoftDeletes;

    protected $table = 'procurement_requests';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'created_by',
        'request_number',
        'requestor_name',
        'requested_at',
        'requestor_department',
        'classification',
        'received_by',
        'procurement_note',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProcurementRequestStatus::class,
            'requested_at' => 'date',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProcurementRequestItem::class)->orderBy('sort_order');
    }

    public function documents(): HasManyThrough
    {
        return $this->hasManyThrough(
            ProcurementRequestDocument::class,
            ProcurementRequestItem::class,
            'procurement_request_id',
            'procurement_request_item_id',
            'id',
            'id',
        )->latest('procurement_request_documents.created_at');
    }
}
