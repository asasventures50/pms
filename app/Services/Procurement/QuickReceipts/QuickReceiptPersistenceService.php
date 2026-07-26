<?php

namespace App\Services\Procurement\QuickReceipts;

use App\Enums\Procurement\PrCompany;
use App\Enums\Procurement\QuickReceipts\QuickReceiptStatus;
use App\Models\Procurement\QuickReceipts\QuickReceipt;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuickReceiptPersistenceService
{
    public function __construct(
        private readonly QuickReceiptCodeGenerator $codeGenerator,
        private readonly QuickReceiptAttachmentStorage $attachments,
        private readonly QuickReceiptDailyLimitService $dailyLimit,
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(User $user, array $attributes, ?UploadedFile $attachment = null): QuickReceipt
    {
        $this->dailyLimit->assertWithinLimit(
            $user,
            (float) $attributes['amount'],
            $attributes['expense_date'],
        );

        return DB::transaction(function () use ($user, $attributes, $attachment) {
            $receipt = QuickReceipt::query()->create([
                'code' => $this->codeGenerator->next(),
                'user_id' => $user->id,
                'title' => $attributes['title'],
                'description' => $attributes['description'] ?? null,
                'amount' => $attributes['amount'],
                'currency_code' => $attributes['currency_code'],
                'expense_date' => $attributes['expense_date'],
                'category_id' => $attributes['category_id'],
                'company_key' => $attributes['company_key'],
                'provider_name' => $attributes['provider_name'] ?? null,
                'status' => QuickReceiptStatus::PendingApproval,
                'submitted_at' => now(),
            ]);

            if ($attachment instanceof UploadedFile) {
                $this->attachments->store($receipt, $attachment);
            }

            return $receipt->fresh(['user', 'approver', 'category']);
        });
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(QuickReceipt $receipt, array $attributes, ?UploadedFile $attachment = null): QuickReceipt
    {
        if ($receipt->isLocked()) {
            throw ValidationException::withMessages([
                'status' => 'Approved receipts are locked and cannot be edited.',
            ]);
        }

        $this->dailyLimit->assertWithinLimit(
            $receipt->user,
            (float) $attributes['amount'],
            $attributes['expense_date'],
            $receipt->id,
        );

        return DB::transaction(function () use ($receipt, $attributes, $attachment) {
            $receipt->fill([
                'title' => $attributes['title'],
                'description' => $attributes['description'] ?? null,
                'amount' => $attributes['amount'],
                'currency_code' => $attributes['currency_code'],
                'expense_date' => $attributes['expense_date'],
                'category_id' => $attributes['category_id'],
                'company_key' => $attributes['company_key'],
                'provider_name' => $attributes['provider_name'] ?? null,
                'status' => QuickReceiptStatus::PendingApproval,
                'submitted_at' => $receipt->submitted_at ?? now(),
                'approved_by' => null,
                'approved_at' => null,
                'rejection_reason' => null,
            ])->save();

            if ($attachment instanceof UploadedFile) {
                $this->attachments->store($receipt, $attachment);
            }

            return $receipt->fresh(['user', 'approver', 'category']);
        });
    }

    public function approve(QuickReceipt $receipt, User $approver): QuickReceipt
    {
        if ($receipt->status !== QuickReceiptStatus::PendingApproval) {
            throw ValidationException::withMessages([
                'status' => 'Only pending receipts can be approved.',
            ]);
        }

        $this->dailyLimit->assertWithinLimit(
            $receipt->user,
            (float) $receipt->amount,
            $receipt->expense_date,
            $receipt->id,
        );

        $receipt->forceFill([
            'status' => QuickReceiptStatus::Approved,
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'rejection_reason' => null,
        ])->save();

        return $receipt->fresh(['user', 'approver', 'category']);
    }

    public function reject(QuickReceipt $receipt, User $approver): QuickReceipt
    {
        if ($receipt->status !== QuickReceiptStatus::PendingApproval) {
            throw ValidationException::withMessages([
                'status' => 'Only pending receipts can be rejected.',
            ]);
        }

        $receipt->forceFill([
            'status' => QuickReceiptStatus::Rejected,
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'rejection_reason' => null,
        ])->save();

        return $receipt->fresh(['user', 'approver', 'category']);
    }

    public function delete(QuickReceipt $receipt): void
    {
        if ($receipt->isLocked()) {
            throw ValidationException::withMessages([
                'status' => 'Approved receipts are locked and cannot be deleted.',
            ]);
        }

        DB::transaction(function () use ($receipt) {
            $this->attachments->purge($receipt);
            $receipt->delete();
        });
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    public static function attributesFromValidated(array $validated, User $user): array
    {
        $currency = strtoupper(trim((string) ($validated['currency_code'] ?? '')));
        if ($currency === '') {
            $currency = $user->defaultCurrencyCode() ?? 'USD';
        }

        return [
            'title' => trim((string) $validated['title']),
            'description' => filled($validated['description'] ?? null)
                ? trim((string) $validated['description'])
                : null,
            'amount' => round((float) $validated['amount'], 2),
            'currency_code' => $currency,
            'expense_date' => $validated['expense_date'],
            'category_id' => (int) $validated['category_id'],
            'company_key' => PrCompany::resolve($validated['company_key'] ?? null)->value,
            'provider_name' => filled($validated['provider_name'] ?? null)
                ? trim((string) $validated['provider_name'])
                : null,
        ];
    }
}
