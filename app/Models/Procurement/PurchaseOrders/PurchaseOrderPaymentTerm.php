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
        'currency_code',
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

    public function displayCurrency(?string $fallback = null): ?string
    {
        $code = strtoupper(trim((string) ($this->currency_code ?? '')));
        if ($code !== '') {
            return $code;
        }

        $fallbackCode = strtoupper(trim((string) ($fallback ?? '')));
        if ($fallbackCode !== '') {
            return $fallbackCode;
        }

        return $this->purchaseOrder?->displayCurrency();
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
