<?php

namespace App\Models\Procurement\ScheduleOfWorks;

use App\Enums\Procurement\Rfqs\RfqTermsLocale;
use App\Enums\Procurement\ScheduleOfWorks\ScheduleOfWorkScope;
use App\Services\Procurement\ScheduleOfWorks\ScheduleOfWorkPrSectionsNormalizer;
use App\Models\Procurement\ProcurementRequests\ProcurementRequest;
use App\Models\Procurement\Vendors\Vendor;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScheduleOfWork extends Model
{
    protected $table = 'schedule_of_works';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'document_number',
        'created_by',
        'recipient_name',
        'project_manager_name',
        'documented_at',
        'po_reference',
        'vendor_id',
        'vendor_company_name',
        'procurement_request_id',
        'currency_code',
        'scope_types',
        'scope_of_work',
        'pr_sections',
        'print_locale',
        'total_price',
        'notes',
        'custom_fees',
    ];

    protected function casts(): array
    {
        return [
            'documented_at' => 'date',
            'scope_types' => 'array',
            'pr_sections' => 'array',
            'notes' => 'array',
            'custom_fees' => 'array',
            'total_price' => 'decimal:2',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function procurementRequest(): BelongsTo
    {
        return $this->belongsTo(ProcurementRequest::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ScheduleOfWorkItem::class)->orderBy('sort_order')->orderBy('line_number');
    }

    public function displayCurrency(): ?string
    {
        $code = trim((string) ($this->currency_code ?? ''));

        return $code !== '' ? strtoupper($code) : null;
    }

    public function printLocaleEnum(): RfqTermsLocale
    {
        return RfqTermsLocale::tryFrom((string) $this->print_locale) ?? RfqTermsLocale::Ar;
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
        return round((float) collect($this->feeLinesForDisplay())->sum('amount'), 2);
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
                $amount = round((float) ($fee['amount'] ?? ($quantity * $unitPrice)), 2);

                if ($description === '' || $amount <= 0) {
                    return null;
                }

                if ($quantity > 0 && $unitPrice > 0) {
                    $amount = round($quantity * $unitPrice, 2);
                }

                return [
                    'project_zone' => trim((string) ($fee['project_zone'] ?? '')),
                    'description' => $description,
                    'quantity' => $quantity,
                    'unit' => trim((string) ($fee['unit'] ?? '')),
                    'unit_price' => $unitPrice,
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
        return $this->feeLinesForDisplay();
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

    public function scopeTypesDisplay(bool $arabic = false): string
    {
        return ScheduleOfWorkScope::display($this->scope_types ?? [], $arabic);
    }

    /**
     * @return array<string, mixed>
     */
    public function displayPrSections(): array
    {
        return ScheduleOfWorkPrSectionsNormalizer::normalize($this->pr_sections ?? []) ?? [];
    }
}
