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
    public const SOURCE_PURCHASE_ORDER = 'purchase_order';

    public const SOURCE_MANUAL = 'manual';

    protected $table = 'invoices';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'invoice_number',
        'source',
        'created_by',
        'recipient_name',
        'project_manager_name',
        'invoiced_at',
        'po_number',
        'vendor_company_name',
        'currency_code',
        'transport_fees',
        'supervision_fees',
        'administrative_fees',
        'logistics_fees',
        'custom_fees',
        'total_price',
        'merged_lines',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'invoiced_at' => 'date',
            'notes' => 'array',
            'custom_fees' => 'array',
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

    public function isManual(): bool
    {
        return $this->source === self::SOURCE_MANUAL;
    }

    public function isFromPurchaseOrder(): bool
    {
        return $this->source === self::SOURCE_PURCHASE_ORDER;
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
        $legacyFixed = (float) $this->transport_fees
            + (float) $this->supervision_fees
            + (float) $this->administrative_fees
            + (float) $this->logistics_fees;
        $custom = (float) collect($this->feeLinesForDisplay())->sum('amount');

        return round($legacyFixed + $custom, 2);
    }

    /**
     * @return list<array{project_zone: string, description: string, quantity: float, unit: string, unit_price: float, amount: float}>
     */
    public function feeLinesForDisplay(): array
    {
        return collect($this->custom_fees ?? [])
            ->map(function (mixed $fee): ?array {
                if (! is_array($fee)) {
                    return null;
                }

                $description = trim((string) ($fee['description'] ?? $fee['label'] ?? ''));
                $quantity = round((float) ($fee['quantity'] ?? 1), 3);
                $unitPrice = round((float) ($fee['unit_price'] ?? 0), 2);
                $marginPercentage = round((float) ($fee['margin_percentage'] ?? 0), 2);
                $amount = round((float) ($fee['amount'] ?? 0), 2);

                if ($description === '') {
                    return null;
                }

                if ($quantity > 0 && $unitPrice >= 0) {
                    $amount = round($quantity * $unitPrice * (1 + $marginPercentage / 100), 2);
                }

                if ($amount <= 0) {
                    return null;
                }

                return [
                    'project_zone' => trim((string) ($fee['project_zone'] ?? '')),
                    'description' => $description,
                    'quantity' => $quantity,
                    'unit' => trim((string) ($fee['unit'] ?? '')),
                    'unit_price' => $unitPrice,
                    'margin_percentage' => $marginPercentage,
                    'amount' => $amount,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return list<array{project_zone: string, description: string, quantity: float, unit: string, unit_price: float, amount: float}>
     */
    public function feeRowsForEdit(): array
    {
        $legacyRows = array_values(array_filter([
            $this->legacyFeeRow('أجور نقل و مواصلات', (float) $this->transport_fees),
            $this->legacyFeeRow('أجور متابعة و اشراف', (float) $this->supervision_fees),
            $this->legacyFeeRow('مصاريف و اجور ادارية', (float) $this->administrative_fees),
            $this->legacyFeeRow('مصاريف و اجور لوجستية', (float) $this->logistics_fees),
        ]));

        return array_merge($legacyRows, $this->feeLinesForDisplay());
    }

    /**
     * @return list<array{project_zone: string, description: string, quantity: float, unit: string, unit_price: float, amount: float}>
     */
    public function feeRowsForPrint(): array
    {
        return $this->feeRowsForEdit();
    }

    /**
     * @return ?array{project_zone: string, description: string, quantity: float, unit: string, unit_price: float, amount: float}
     */
    private function legacyFeeRow(string $description, float $amount): ?array
    {
        if ($amount <= 0) {
            return null;
        }

        return [
            'project_zone' => '',
            'description' => $description,
            'quantity' => 1,
            'unit' => '',
            'unit_price' => $amount,
            'margin_percentage' => 0,
            'amount' => $amount,
        ];
    }

    /**
     * @return list<string>
     */
    public function displayNotes(): array
    {
        return collect($this->notes ?? [])
            ->map(static fn (mixed $note): string => trim((string) $note))
            ->filter()
            ->values()
            ->all();
    }
}
