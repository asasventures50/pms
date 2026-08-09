<?php

namespace App\Models\Procurement\Projects;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Zone extends Model
{
    use LogsActivity, SoftDeletes;

    protected static string $activityLogKey = 'zone';

    protected $table = 'zones';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'project_id',
        'code',
        'name',
        'status',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
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
