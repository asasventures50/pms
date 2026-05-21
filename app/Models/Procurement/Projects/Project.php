<?php

namespace App\Models\Procurement\Projects;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;

    protected $table = 'projects';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'name',
        'status',
    ];

    public function zones(): HasMany
    {
        return $this->hasMany(Zone::class)->orderBy('name');
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<static>  $query
     * @return \Illuminate\Database\Eloquent\Builder<static>
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
