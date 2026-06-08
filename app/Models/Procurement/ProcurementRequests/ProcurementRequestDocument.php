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
        'procurement_request_item_id',
        'document_type',
        'file_name',
        'file_path',
        'file_description',
    ];

    /**
     * @var list<string>
     */
    protected $appends = ['url'];

    public static function isExternalUrl(?string $path): bool
    {
        return is_string($path)
            && $path !== ''
            && filter_var($path, FILTER_VALIDATE_URL) !== false;
    }

    protected function url(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->file_path) {
                    return null;
                }

                if (self::isExternalUrl($this->file_path)) {
                    return $this->file_path;
                }

                /** @var FilesystemAdapter $disk */
                $disk = Storage::disk('s3');

                return $disk->url($this->file_path);
            },
        );
    }

    public function procurementRequest(): BelongsTo
    {
        return $this->belongsTo(ProcurementRequest::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(ProcurementRequestItem::class, 'procurement_request_item_id');
    }
}
