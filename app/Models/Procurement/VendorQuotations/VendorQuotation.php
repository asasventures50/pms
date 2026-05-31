<?php

namespace App\Models\Procurement\VendorQuotations;

use App\Models\Concerns\LogsActivity;
use App\Models\Procurement\Rfqs\Rfq;
use App\Models\Procurement\Vendors\Vendor;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorQuotation extends Model
{
    use LogsActivity, SoftDeletes;

    protected static string $activityLogKey = 'vendor_quotation';

    protected $table = 'vendor_quotations';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'rfq_id',
        'created_by',
        'quotation_number',
        'vendor_id',
        'vendor_company_name',
        'vendor_contact',
        'vendor_email',
        'vendor_phone',
        'vendor_address',
        'grand_total',
        'payment_method',
        'notes',
        'documents_attached',
        'vendor_rep_name',
        'vendor_rep_signature',
        'vendor_rep_signed_at',
    ];

    protected function casts(): array
    {
        return [
            'grand_total' => 'decimal:2',
            'vendor_rep_signed_at' => 'date',
            'documents_attached' => 'array',
        ];
    }

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(Rfq::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(VendorQuotationItem::class)->orderBy('sort_order');
    }

    public function activityLogLabel(): string
    {
        return $this->quotation_number ?: 'Vendor quotation #'.$this->id;
    }
}
