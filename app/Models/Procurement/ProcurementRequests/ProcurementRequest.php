<?php

namespace App\Models\Procurement\ProcurementRequests;

use App\Enums\Procurement\ProcurementRequests\ProcurementRequestStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'required_delivery_date',
        'delivery_location',
        'delivery_completed',
        'classification',
        'supporting_document_path',
        'supporting_document_name',
        'received_by',
        'procurement_note',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProcurementRequestStatus::class,
            'requested_at' => 'date',
            'required_delivery_date' => 'date',
            'delivery_completed' => 'boolean',
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

    public function supportingDocumentUrl(): ?string
    {
        $path = $this->supporting_document_path;

        if ($path === null || $path === '') {
            return null;
        }

        return Storage::disk('public')->url($path);
    }
}
