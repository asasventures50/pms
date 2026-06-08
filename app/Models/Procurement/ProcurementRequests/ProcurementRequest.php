<?php

namespace App\Models\Procurement\ProcurementRequests;

use App\Enums\Procurement\ProcurementRequests\ProcurementRequestStatus;
use App\Models\Procurement\Projects\Project;
use App\Models\Procurement\Projects\Zone;
use App\Models\Procurement\Vendors\Category;
use App\Models\Procurement\Vendors\Subcategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProcurementRequest extends Model
{
    use SoftDeletes;

    protected $table = 'procurement_requests';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'created_by',
        'request_number',
        'requestor_name',
        'requested_at',
        'requestor_department',
        'project_id',
        'zone_id',
        'category_id',
        'subcategory_id',
        'procurement_types',
        'geographic_scopes',
        'vendor_types',
        'justification',
        'delivery_lead_time_days',
        'delivery_location',
        'flexible_delivery_date',
        'currency_code',
        'samples_required',
        'scope_of_work',
        'nda_required',
        'primary_insurance_applicable',
        'final_insurance_applicable',
        'warranty_years',
        'warranty_coverage',
        'classification',
        'received_by',
        'procurement_note',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProcurementRequestStatus::class,
            'requested_at' => 'date',
            'procurement_types' => 'array',
            'geographic_scopes' => 'array',
            'vendor_types' => 'array',
            'flexible_delivery_date' => 'boolean',
            'samples_required' => 'boolean',
            'nda_required' => 'boolean',
            'primary_insurance_applicable' => 'boolean',
            'final_insurance_applicable' => 'boolean',
            'warranty_years' => 'decimal:1',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProcurementRequestItem::class)->orderBy('sort_order');
    }

    public function paymentTerms(): HasMany
    {
        return $this->hasMany(ProcurementRequestPaymentTerm::class)->orderBy('sort_order');
    }

    public function retentions(): HasMany
    {
        return $this->hasMany(ProcurementRequestRetention::class)->orderBy('sort_order');
    }

    public function timelineEntries(): HasMany
    {
        return $this->hasMany(ProcurementRequestTimelineEntry::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(ProcurementRequestApproval::class);
    }

    public function headerDocuments(): HasMany
    {
        return $this->hasMany(ProcurementRequestDocument::class)
            ->whereNull('procurement_request_item_id')
            ->latest();
    }

    public function documents(): HasManyThrough
    {
        return $this->hasManyThrough(
            ProcurementRequestDocument::class,
            ProcurementRequestItem::class,
            'procurement_request_id',
            'procurement_request_item_id',
            'id',
            'id',
        )->latest('procurement_request_documents.created_at');
    }
}
