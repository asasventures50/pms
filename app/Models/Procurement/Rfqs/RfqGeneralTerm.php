<?php

namespace App\Models\Procurement\Rfqs;

use App\Support\Procurement\ProcurementScopeType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class RfqGeneralTerm extends Model
{
    protected $table = 'rfq_general_terms';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'scope_types',
        'body_ar',
        'body_en',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'scope_types' => 'array',
            'sort_order' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return list<string>
     */
    public function resolvedScopeTypes(): array
    {
        return ProcurementScopeType::selectedValues($this->scope_types);
    }

    public function isGlobal(): bool
    {
        return $this->resolvedScopeTypes() === [];
    }

    public function appliesToScopeType(string $scopeType): bool
    {
        return in_array($scopeType, $this->resolvedScopeTypes(), true);
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeGlobal(Builder $query): void
    {
        $query->where(function (Builder $q) {
            $q->whereNull('scope_types')->orWhereJsonLength('scope_types', 0);
        });
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeMatchingScopeType(Builder $query, string $scopeType): void
    {
        $query->whereJsonContains('scope_types', $scopeType);
    }

    /**
     * @param  Builder<self>  $query
     * @param  list<string>  $scopeTypes
     */
    public function scopeMatchingAnyScopeType(Builder $query, array $scopeTypes): void
    {
        $scopeTypes = ProcurementScopeType::selectedValues($scopeTypes);

        if ($scopeTypes === []) {
            return;
        }

        $query->where(function (Builder $q) use ($scopeTypes) {
            foreach ($scopeTypes as $scopeType) {
                $q->orWhereJsonContains('scope_types', $scopeType);
            }
        });
    }
}
