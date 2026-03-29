<?php

namespace App\Models\Procurement\Vendors;

use App\Models\Geo\City;
use App\Models\Geo\Country;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorLocation extends Model
{
    protected $table = 'vendor_locations';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'vendor_id',
        'country_id',
        'city_id',
        'address',
        'phone',
        'whatsapp',
        'is_primary',
        'notes',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }
}
