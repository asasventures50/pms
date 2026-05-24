<?php

namespace App\Models\Procurement\Rfqs;

use Illuminate\Database\Eloquent\Model;

class RfqGeneralTerm extends Model
{
    protected $table = 'rfq_general_terms';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'scope_type',
        'body_ar',
        'body_en',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
