<?php

namespace App\Models\Procurement\Rfqs;

use App\Enums\Procurement\Rfqs\RfqVendorQuotationInviteLocale;
use App\Enums\Procurement\Rfqs\RfqVendorQuotationInviteStatus;
use App\Models\Procurement\VendorQuotations\VendorQuotation;
use App\Models\Procurement\Vendors\Vendor;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RfqVendorQuotationInvite extends Model
{
    protected $table = 'rfq_vendor_quotation_invites';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'rfq_id',
        'vendor_id',
        'token',
        'ui_locale',
        'include_terms',
        'status',
        'vendor_quotation_id',
        'created_by',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'ui_locale' => RfqVendorQuotationInviteLocale::class,
            'status' => RfqVendorQuotationInviteStatus::class,
            'include_terms' => 'boolean',
            'submitted_at' => 'datetime',
        ];
    }

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(Rfq::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function vendorQuotation(): BelongsTo
    {
        return $this->belongsTo(VendorQuotation::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isPending(): bool
    {
        return $this->status === RfqVendorQuotationInviteStatus::Pending;
    }

    public function isSubmitted(): bool
    {
        return $this->status === RfqVendorQuotationInviteStatus::Submitted;
    }

    public function isReadOnly(): bool
    {
        return ! $this->isPending();
    }

    public function getRouteKeyName(): string
    {
        return 'token';
    }

    public function publicUrl(): string
    {
        return route('vendor-quotation-invite.show', $this);
    }
}
