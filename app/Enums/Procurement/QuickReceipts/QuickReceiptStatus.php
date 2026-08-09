<?php

namespace App\Enums\Procurement\QuickReceipts;

enum QuickReceiptStatus: string
{
    case Draft = 'draft';
    case PendingApproval = 'pending_approval';
    case Approved = 'approved';
    case Signed = 'signed';
    case Rejected = 'rejected';

    /**
     * Statuses that count toward an employee's daily spending limit.
     *
     * @return list<self>
     */
    public static function countsTowardDailyLimit(): array
    {
        return [
            self::PendingApproval,
            self::Approved,
            self::Signed,
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case) => $case->value, self::cases());
    }

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::PendingApproval => 'Pending approval',
            self::Approved => 'Approved',
            self::Signed => 'Signed',
            self::Rejected => 'Rejected',
        };
    }

    /** Locked forever once approved or signed — no edit and no delete. */
    public function isLocked(): bool
    {
        return $this === self::Approved || $this === self::Signed;
    }

    /** Editable until approved (pending, rejected, or leftover draft). */
    public function isEditable(): bool
    {
        return ! $this->isLocked();
    }

    /** Approved receipts await the signed scan; signed receipts stay printable. */
    public function isPrintable(): bool
    {
        return $this === self::Approved || $this === self::Signed;
    }

    public function isSignable(): bool
    {
        return $this === self::Approved;
    }
}
