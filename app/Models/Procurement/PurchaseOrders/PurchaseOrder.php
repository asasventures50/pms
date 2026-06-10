<?php

namespace App\Models\Procurement\PurchaseOrders;

use App\Enums\Procurement\PurchaseOrders\PaymentStatus;
use App\Enums\Procurement\PurchaseOrders\PurchaseOrderStatus;
use App\Models\Concerns\LogsActivity;
use App\Models\Procurement\ProcurementRequests\ProcurementRequest;
use App\Models\Procurement\Vendors\Vendor;
use App\Models\User;
use App\Services\Procurement\PurchaseOrders\VendorPurchaseOrderSnapshot;
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
        'company_name',
        'company_address',
        'company_phone',
        'company_email',
        'company_website',
        'po_number',
        'title',
        'description',
        'ordered_at',
        'delivered_at',
        'notes',
        'status',
        'vendor_id',
        'procurement_request_id',
        'vendor_code',
        'vendor_language',
        'vendor_description',
        'vendor_profile_notes',
        'vendor_company_name',
        'vendor_contact',
        'vendor_primary_contact_position',
        'vendor_primary_contact_phone',
        'vendor_primary_contact_email',
        'vendor_email',
        'vendor_phone',
        'vendor_whatsapp',
        'vendor_telegram',
        'vendor_company_email',
        'vendor_website',
        'vendor_address',
        'vendor_company_type',
        'vendor_coverage_type',
        'vendor_tax_number',
        'vendor_registration_number',
        'vendor_license_number',
        'vendor_business_types',
        'vendor_categories_summary',
        'vendor_classification',
        'delivery_contact_name',
        'delivery_contact_phone',
        'delivery_contact_email',
        'currency_code',
        'total_price',
        'delivery_fee',
        'discount',
        'payment_terms',
        'retentions',
        'show_retention',
        'after_sale_service_applicable',
        'warranty_years',
        'warranty_coverage',
        'show_maintenance',
        'delivery_time',
        'delivery_location',
        'handover_at',
        'dismantling_at',
        'terms',
        'terms_locale',
        'payment_status',
        'vendor_signature',
        'vendor_signed_at',
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
            'handover_at' => 'date',
            'dismantling_at' => 'date',
            'vendor_signed_at' => 'date',
            'procurement_signed_at' => 'date',
            'finance_signed_at' => 'date',
            'ceo_signed_at' => 'date',
            'total_price' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'discount' => 'decimal:2',
            'terms' => 'array',
            'retentions' => 'array',
            'show_retention' => 'boolean',
            'after_sale_service_applicable' => 'boolean',
            'warranty_years' => 'decimal:1',
            'show_maintenance' => 'boolean',
            // Legacy — kept in DB for old P.O. records; not shown or edited in UI.
            'primary_insurance_applicable' => 'boolean',
            'final_insurance_applicable' => 'boolean',
            'show_insurance' => 'boolean',
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

    public function procurementRequest(): BelongsTo
    {
        return $this->belongsTo(ProcurementRequest::class, 'procurement_request_id');
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

    /**
     * Vendor lines for show/print — only fields with values; empty fields are omitted.
     *
     * @return array<string, string>
     */
    public function vendorDisplayRows(): array
    {
        $snapshot = $this->vendorSnapshotFallback();

        $name = $this->vendorFieldValue('vendor_company_name', $snapshot);
        if ($name === '') {
            $name = trim((string) ($this->vendor?->name ?? ''));
        }

        $classification = $this->vendorFieldValue('vendor_classification', $snapshot);
        if ($classification === '') {
            $classification = $this->legacyClassificationSummary();
        }
        if ($classification === '' && $snapshot !== []) {
            $classification = trim((string) ($snapshot['vendor_classification'] ?? ''));
        }

        $candidates = [
            'Name' => $name,
            'Email' => $this->vendorFieldValue('vendor_email', $snapshot),
            'Phone' => $this->vendorFieldValue('vendor_phone', $snapshot),
            'WhatsApp' => $this->vendorFieldValue('vendor_whatsapp', $snapshot),
            'Position' => $this->vendorFieldValue('vendor_primary_contact_position', $snapshot),
            'Classification' => $classification,
        ];

        return array_filter(
            $candidates,
            static fn (string $value) => $value !== '',
        );
    }

    /**
     * @return array<string, string|null>
     */
    private function vendorSnapshotFallback(): array
    {
        if (! $this->vendor_id) {
            return [];
        }

        $this->loadMissing([
            'vendor.primaryLocation',
            'vendor.businessTypes',
            'vendor.vendorCategories.category',
            'vendor.vendorCategories.subcategory',
        ]);

        if (! $this->vendor) {
            return [];
        }

        return VendorPurchaseOrderSnapshot::fromVendor($this->vendor);
    }

    /**
     * @param  array<string, string|null>  $snapshot
     */
    private function vendorFieldValue(string $attribute, array $snapshot): string
    {
        $stored = trim((string) ($this->{$attribute} ?? ''));
        if ($stored !== '') {
            return $stored;
        }

        return trim((string) ($snapshot[$attribute] ?? ''));
    }

    private function legacyClassificationSummary(): string
    {
        $parts = array_filter([
            trim((string) ($this->vendor_company_type ?? '')),
            trim((string) ($this->vendor_coverage_type ?? '')),
            trim((string) ($this->vendor_business_types ?? '')),
            trim((string) ($this->vendor_categories_summary ?? '')),
        ]);

        return implode(' · ', $parts);
    }
}
