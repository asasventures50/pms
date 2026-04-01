<?php

namespace App\Models\Procurement\Vendors;

use App\Enums\Procurement\Vendors\CompanyType;
use App\Enums\Procurement\Vendors\CoverageType;
use App\Enums\Procurement\Vendors\LeadTimeRange;
use App\Enums\Procurement\Vendors\PaymentMethod;
use App\Enums\Procurement\Vendors\PricingFrequency;
use App\Enums\Procurement\Vendors\VendorLanguage;
use App\Enums\Procurement\Vendors\VendorStatus;
use App\Models\Procurement\Vendors\VendorBusinessType as VendorBusinessTypeModel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use SoftDeletes;

    protected $table = 'vendors';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'vendor_code',
        'name',
        'language',
        'description',
        'notes',
        'phone',
        'whatsapp',
        'telegram',
        'email',
        'website',
        'facebook_url',
        'instagram_url',
        'primary_contact_name',
        'primary_contact_position',
        'primary_contact_phone',
        'primary_contact_email',
        'secondary_contact_name',
        'secondary_contact_position',
        'secondary_contact_phone',
        'secondary_contact_email',
        'rfq_method',
        'pricing_frequency',
        'delivery_lead_time',
        'execution_lead_time',
        'payment_method',
        'payment_terms',
        'commercial_terms',
        'technical_capabilities',
        'bulletin_price_validity_days',
        'currency_code',
        'company_type',
        'status',
        'coverage_type',
        'tax_number',
        'registration_number',
        'license_number',
        'rating',
    ];

    protected function casts(): array
    {
        return [
            'language' => VendorLanguage::class,
            'rfq_method' => 'array',
            'pricing_frequency' => PricingFrequency::class,
            'delivery_lead_time' => LeadTimeRange::class,
            'execution_lead_time' => LeadTimeRange::class,
            'payment_method' => PaymentMethod::class,
            'company_type' => CompanyType::class,
            'status' => VendorStatus::class,
            'coverage_type' => CoverageType::class,
            'rating' => 'integer',
        ];
    }

    public function locations(): HasMany
    {
        return $this->hasMany(VendorLocation::class, 'vendor_id');
    }

    public function primaryLocation(): HasOne
    {
        return $this->hasOne(VendorLocation::class, 'vendor_id')->where('is_primary', true);
    }

    public function brochures(): HasMany
    {
        return $this->hasMany(VendorBrochure::class, 'vendor_id');
    }

    /**
     * True when the vendor has at least one brochure record.
     * Uses `brochures_count` when present (e.g. from withCount) to avoid extra queries.
     */
    protected function hasBrochures(): Attribute
    {
        return Attribute::get(function (): bool {
            if (array_key_exists('brochures_count', $this->attributes)) {
                return (int) $this->attributes['brochures_count'] > 0;
            }

            if ($this->relationLoaded('brochures')) {
                return $this->brochures->isNotEmpty();
            }

            return $this->brochures()->exists();
        });
    }

    public function businessTypes(): HasMany
    {
        return $this->hasMany(VendorBusinessTypeModel::class, 'vendor_id');
    }

    public function vendorCategories(): HasMany
    {
        return $this->hasMany(VendorCategory::class, 'vendor_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'vendor_categories', 'vendor_id', 'category_id')
            ->withPivot(['subcategory_id', 'is_primary'])
            ->withTimestamps();
    }

    public function subcategories(): BelongsToMany
    {
        return $this->belongsToMany(Subcategory::class, 'vendor_categories', 'vendor_id', 'subcategory_id')
            ->withPivot(['category_id', 'is_primary'])
            ->withTimestamps();
    }
}
