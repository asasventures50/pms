<?php

namespace App\Models\Geo;

use App\Models\Procurement\Vendors\VendorLocation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Country extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name_ar',
        'name_en',
        'name',
        'iso_code',
        'flag_emoji',
        'status',
    ];

    public function cities(): HasMany
    {
        return $this->hasMany(City::class, 'country_id');
    }

    public function vendorLocations(): HasMany
    {
        return $this->hasMany(VendorLocation::class, 'country_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
