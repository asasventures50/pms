<?php

namespace App\Models\Procurement\Rfqs;

use App\Enums\Procurement\Rfqs\RfqStatus;
use App\Models\Concerns\LogsActivity;
use App\Models\Procurement\VendorQuotations\VendorQuotation;
use App\Models\Procurement\Vendors\Vendor;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rfq extends Model
{
    use LogsActivity, SoftDeletes;

    protected static string $activityLogKey = 'rfq';

    protected $table = 'rfqs';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'created_by',
        'company_name',
        'company_phone',
        'company_email',
        'company_address',
        'company_website',
        'rfq_number',
        'revision_number',
        'vendor_id',
        'vendor_company_name',
        'vendor_contact',
        'vendor_email',
        'vendor_phone',
        'vendor_address',
        'issue_date',
        'submission_deadline',
        'submission_deadline_at',
        'submission_timezone',
        'quotation_validity',
        'payment_method',
        'grand_total',
        'status',
        'selected_vendor_quotation_id',
        'selected_by',
        'selected_at',
        'terms',
        'terms_locale',
        'payment_terms',
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
            'submission_deadline_at' => 'datetime',
            'revision_number' => 'integer',
            'vendor_rep_signed_at' => 'date',
            'grand_total' => 'decimal:2',
            'selected_at' => 'datetime',
            'terms' => 'array',
            'payment_terms' => 'array',
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

    public function vendorQuotations(): HasMany
    {
        return $this->hasMany(VendorQuotation::class)->latest();
    }

    public function vendorQuotationInvites(): HasMany
    {
        return $this->hasMany(RfqVendorQuotationInvite::class)->latest();
    }

    public function selectedVendorQuotation(): BelongsTo
    {
        return $this->belongsTo(VendorQuotation::class, 'selected_vendor_quotation_id');
    }

    public function selectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'selected_by');
    }
}
