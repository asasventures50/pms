<?php

namespace App\Models\Procurement\PurchaseOrders;

use App\Enums\Procurement\PurchaseOrders\PaymentStatus;
use App\Enums\Procurement\PurchaseOrders\PurchaseOrderStatus;
use App\Models\Procurement\Vendors\Vendor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use SoftDeletes;

    protected $table = 'purchase_orders';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'po_number',
        'title',
        'description',
        'ordered_at',
        'delivered_at',
        'notes',
        'status',
        'vendor_id',
        'total_price',
        'payment_status',
    ];

    protected function casts(): array
    {
        return [
            'status'         => PurchaseOrderStatus::class,
            'payment_status' => PaymentStatus::class,
            'ordered_at'     => 'date',
            'delivered_at'   => 'date',
            'total_price'    => 'decimal:2',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }
}
