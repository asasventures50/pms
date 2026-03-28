<?php

namespace App\Models\Procurement\Vendors;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorBrochure extends Model
{
    protected $table = 'vendor_brochures';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'vendor_id',
        'category_id',
        'subcategory_id',
        'file_name',
        'file_path',
        'file_type',
        'notes',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Subcategory::class, 'subcategory_id');
    }
}
