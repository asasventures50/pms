<?php

namespace App\Models\Procurement\ProcurementRequests;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

class ProcurementRequestDocument extends Model
{
    protected $table = 'procurement_request_documents';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'procurement_request_id',
        'file_name',
        'file_path',
    ];

    /**
     * @var list<string>
     */
    protected $appends = ['url'];

    protected function url(): Attribute
    {
        return Attribute::make(
            get: function (): ?string {
                if ($this->file_path === null || $this->file_path === '') {
                    return null;
                }

                /** @var FilesystemAdapter $disk */
                $disk = Storage::disk('public');

                return $disk->url($this->file_path);
            },
        );
    }

    public function procurementRequest(): BelongsTo
    {
        return $this->belongsTo(ProcurementRequest::class);
    }
}
