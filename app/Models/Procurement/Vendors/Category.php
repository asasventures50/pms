<?php

namespace App\Models\Procurement\Vendors;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;

    protected $table = 'categories';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name_en',
        'name_ar',
        'slug',
        'description',
        'status',
    ];

    public function subcategories(): HasMany
    {
        return $this->hasMany(Subcategory::class, 'category_id');
    }

    public function vendors(): BelongsToMany
    {
        return $this->belongsToMany(Vendor::class, 'vendor_categories', 'category_id', 'vendor_id')
            ->withPivot(['subcategory_id', 'is_primary'])
            ->withTimestamps();
    }
}
