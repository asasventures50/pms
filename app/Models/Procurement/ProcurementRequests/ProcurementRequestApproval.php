<?php

namespace App\Models\Procurement\ProcurementRequests;

use App\Enums\Procurement\ProcurementRequests\ProcurementApprovalRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcurementRequestApproval extends Model
{
    protected $table = 'procurement_request_approvals';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'procurement_request_id',
        'role',
        'name',
        'signature',
        'signed_at',
    ];

    protected function casts(): array
    {
        return [
            'role' => ProcurementApprovalRole::class,
            'signed_at' => 'date',
        ];
    }

    public function procurementRequest(): BelongsTo
    {
        return $this->belongsTo(ProcurementRequest::class);
    }
}
