<?php

namespace App\Models\Procurement\Vendors;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subcategory extends Model
{
    use SoftDeletes;

    protected $table = 'subcategories';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'category_id',
        'name_en',
        'name_ar',
        'slug',
        'description',
        'status',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function vendors(): BelongsToMany
    {
        return $this->belongsToMany(Vendor::class, 'vendor_categories', 'subcategory_id', 'vendor_id')
            ->withPivot(['category_id', 'is_primary'])
            ->withTimestamps();
    }
}
