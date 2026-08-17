<?php

namespace App\Models\Procurement\PurchaseOrders;

use App\Models\Procurement\Invoices\Invoice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderPaymentTerm extends Model
{
    protected $table = 'purchase_order_payment_terms';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'purchase_order_id',
        'milestone',
        'percentage',
        'amount',
        'notes',
        'invoice_id',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'percentage' => 'decimal:2',
            'amount' => 'decimal:2',
        ];
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function isInvoiced(): bool
    {
        return $this->invoice_id !== null;
    }

    public function resolvedAmount(float $poTotal): float
    {
        if ($this->amount !== null && $this->amount !== '') {
            $amount = round((float) $this->amount, 2);
            if ($amount > 0) {
                return $amount;
            }
        }

        if ($this->percentage !== null && $this->percentage !== '' && $poTotal > 0) {
            return round(((float) $this->percentage / 100) * $poTotal, 2);
        }

        return 0.0;
    }

    public function percentageLabel(): ?string
    {
        if ($this->percentage === null || $this->percentage === '') {
            return null;
        }

        return rtrim(rtrim(number_format((float) $this->percentage, 2), '0'), '.').'%';
    }
}
