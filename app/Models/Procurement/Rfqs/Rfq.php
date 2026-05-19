<?php

namespace App\Models\Procurement\Rfqs;

use App\Enums\Procurement\Rfqs\RfqStatus;
use App\Models\Procurement\Vendors\Vendor;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rfq extends Model
{
    use SoftDeletes;

    protected $table = 'rfqs';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'created_by',
        'rfq_number',
        'vendor_id',
        'vendor_company_name',
        'vendor_contact',
        'vendor_email',
        'vendor_phone',
        'vendor_address',
        'issue_date',
        'submission_deadline',
        'payment_method',
        'grand_total',
        'status',
        'vendor_rep_name',
        'vendor_rep_signature',
        'vendor_rep_signed_at',
        'vendor_company_stamp',
    ];

    protected function casts(): array
    {
        return [
            'status' => RfqStatus::class,
            'issue_date' => 'date',
            'submission_deadline' => 'date',
            'vendor_rep_signed_at' => 'date',
            'grand_total' => 'decimal:2',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(RfqItem::class)->orderBy('sort_order');
    }
}
