<?php

namespace App\Models\Procurement\QuickReceipts;

use App\Enums\Procurement\PrCompany;
use App\Enums\Procurement\QuickReceipts\QuickReceiptStatus;
use App\Models\Concerns\LogsActivity;
use App\Models\Procurement\Vendors\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class QuickReceipt extends Model
{
    use LogsActivity;

    protected static string $activityLogKey = 'quick_receipt';

    protected $table = 'quick_receipts';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'user_id',
        'title',
        'description',
        'amount',
        'currency_code',
        'expense_date',
        'category_id',
        'company_key',
        'provider_name',
        'attachment_path',
        'attachment_original_name',
        'status',
        'submitted_at',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => QuickReceiptStatus::class,
            'expense_date' => 'date',
            'amount' => 'decimal:2',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function categoryLabel(): string
    {
        $category = $this->category;

        if ($category === null) {
            return '—';
        }

        $ar = trim((string) ($category->name_ar ?? ''));
        $en = trim((string) ($category->name_en ?? ''));

        if ($ar !== '' && $en !== '') {
            return $ar.' — '.$en;
        }

        return $ar !== '' ? $ar : ($en !== '' ? $en : '—');
    }

    public function company(): PrCompany
    {
        return PrCompany::resolve($this->company_key);
    }

    public function displayCurrency(): ?string
    {
        $code = trim((string) ($this->currency_code ?? ''));

        return $code !== '' ? strtoupper($code) : null;
    }

    public function hasAttachment(): bool
    {
        return trim((string) ($this->attachment_path ?? '')) !== '';
    }

    public function attachmentUrl(): ?string
    {
        if (! $this->hasAttachment()) {
            return null;
        }

        try {
            return Storage::disk('s3')->url($this->attachment_path);
        } catch (\Throwable) {
            return null;
        }
    }

    public function isApproved(): bool
    {
        return ($this->status ?? null) === QuickReceiptStatus::Approved;
    }

    public function isLocked(): bool
    {
        return ($this->status ?? QuickReceiptStatus::Draft)->isLocked();
    }

    public function isEditable(): bool
    {
        return ! $this->isLocked();
    }

    public function isPrintable(): bool
    {
        return ($this->status ?? QuickReceiptStatus::Draft)->isPrintable();
    }

    public function formatAmount(): string
    {
        $formatted = number_format((float) $this->amount, 2);
        $currency = $this->displayCurrency();

        return $currency !== null ? "{$formatted} {$currency}" : $formatted;
    }

    public function activityLogLabel(): string
    {
        $code = trim((string) ($this->code ?? ''));

        if ($code !== '') {
            return "quick receipt {$code}";
        }

        return parent::activityLogLabel();
    }
}
