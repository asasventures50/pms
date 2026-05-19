<?php

namespace App\Models\Procurement\PurchaseOrders;

use App\Enums\Procurement\PurchaseOrders\PaymentStatus;
use App\Enums\Procurement\PurchaseOrders\PurchaseOrderStatus;
use App\Models\Concerns\LogsActivity;
use App\Models\Procurement\Vendors\Vendor;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use LogsActivity, SoftDeletes;

    protected static string $activityLogKey = 'po';

    protected $table = 'purchase_orders';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'created_by',
        'po_number',
        'title',
        'description',
        'ordered_at',
        'delivered_at',
        'notes',
        'status',
        'vendor_id',
        'vendor_company_name',
        'vendor_contact',
        'vendor_email',
        'vendor_phone',
        'vendor_address',
        'currency_code',
        'total_price',
        'payment_terms',
        'delivery_time',
        'delivery_location',
        'payment_status',
        'procurement_signature',
        'procurement_signed_at',
        'finance_signature',
        'finance_signed_at',
        'ceo_signature',
        'ceo_signed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => PurchaseOrderStatus::class,
            'payment_status' => PaymentStatus::class,
            'ordered_at' => 'date',
            'delivered_at' => 'date',
            'procurement_signed_at' => 'date',
            'finance_signed_at' => 'date',
            'ceo_signed_at' => 'date',
            'total_price' => 'decimal:2',
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
        return $this->hasMany(PurchaseOrderItem::class)->orderBy('sort_order');
    }

    public function displayCurrency(): ?string
    {
        $code = trim((string) ($this->currency_code ?? ''));

        return $code !== '' ? strtoupper($code) : null;
    }

    public function formatMoneyAmount(float|string|null $amount): string
    {
        if ($amount === null || $amount === '') {
            return '—';
        }

        $formatted = number_format((float) $amount, 2);
        $currency = $this->displayCurrency();

        return $currency ? "{$formatted} {$currency}" : $formatted;
    }
}
