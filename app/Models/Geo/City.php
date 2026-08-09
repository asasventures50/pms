<?php

namespace App\Models\Geo;

use App\Models\Concerns\LogsActivity;
use App\Models\Procurement\Vendors\VendorLocation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class City extends Model
{
    use LogsActivity, SoftDeletes;

    protected static string $activityLogKey = 'city';

    protected $fillable = [
        'country_id',
        'name_ar',
        'name_en',
        'name',
        'status',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function vendorLocations(): HasMany
    {
        return $this->hasMany(VendorLocation::class, 'city_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
