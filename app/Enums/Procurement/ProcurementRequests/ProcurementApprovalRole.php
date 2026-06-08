<?php

namespace App\Enums\Procurement\ProcurementRequests;

enum ProcurementApprovalRole: string
{
    case Requester = 'requester';
    case Procurement = 'procurement';
    case GeneralManager = 'general_manager';
    case ReceivedBy = 'received_by';

    public function label(): string
    {
        return match ($this) {
            self::Requester => 'Requester',
            self::Procurement => 'Procurement',
            self::GeneralManager => 'General Manager',
            self::ReceivedBy => 'Received by',
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case) => $case->value, self::cases());
    }
}
