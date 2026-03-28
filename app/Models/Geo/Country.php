<?php

namespace App\Models\Geo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    protected $fillable = [
        'name',
        'iso_code',
        'flag_emoji',
    ];

    public function cities(): HasMany
    {
        return $this->hasMany(City::class, 'country_id');
    }
}
