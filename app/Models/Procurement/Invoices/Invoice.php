<?php

namespace App\Models\Procurement\Invoices;

use App\Models\Procurement\PurchaseOrders\PurchaseOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $table = 'invoices';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'invoice_number',
        'created_by',
        'recipient_name',
        'invoiced_at',
        'po_number',
        'vendor_company_name',
        'currency_code',
        'transport_fees',
        'supervision_fees',
        'administrative_fees',
        'logistics_fees',
        'total_price',
        'merged_lines',
    ];

    protected function casts(): array
    {
        return [
            'invoiced_at' => 'date',
            'transport_fees' => 'decimal:2',
            'supervision_fees' => 'decimal:2',
            'administrative_fees' => 'decimal:2',
            'logistics_fees' => 'decimal:2',
            'total_price' => 'decimal:2',
            'merged_lines' => 'boolean',
        ];
    }

    public function purchaseOrders(): BelongsToMany
    {
        return $this->belongsToMany(PurchaseOrder::class, 'invoice_purchase_orders')
            ->withTimestamps();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('sort_order')->orderBy('line_number');
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

    public function linesSubtotal(): float
    {
        return round((float) $this->items->sum('line_total'), 2);
    }

    public function feesSubtotal(): float
    {
        return round(
            (float) $this->transport_fees
            + (float) $this->supervision_fees
            + (float) $this->administrative_fees
            + (float) $this->logistics_fees,
            2,
        );
    }
}
