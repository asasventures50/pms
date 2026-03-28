<?php

namespace App\Models\Procurement\Vendors;

use App\Enums\Procurement\Vendors\VendorBusinessType as VendorBusinessTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorBusinessType extends Model
{
    protected $table = 'vendor_business_types';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'vendor_id',
        'business_type',
    ];

    protected function casts(): array
    {
        return [
            'business_type' => VendorBusinessTypeEnum::class,
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }
}
