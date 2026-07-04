<?php

namespace App\Enums\Procurement\Flow;

enum FlowStageKey: string
{
    case Pr = 'pr';
    case Rfq = 'rfq';
    case Quotations = 'quotations';
    case Selection = 'selection';
    case Po = 'po';
    case Invoice = 'invoice';

    public function label(): string
    {
        return match ($this) {
            self::Pr => 'PR',
            self::Rfq => 'RFQ',
            self::Quotations => 'Quotations',
            self::Selection => 'Selection',
            self::Po => 'PO',
            self::Invoice => 'Invoice',
        };
    }

    public function statusLabel(): string
    {
        return match ($this) {
            self::Pr => 'Purchase request',
            self::Rfq => 'Request for quotation',
            self::Quotations => 'Collecting quotations',
            self::Selection => 'Quotation selection',
            self::Po => 'Purchase order',
            self::Invoice => 'Invoice',
        };
    }

    /**
     * @return list<self>
     */
    public static function ordered(): array
    {
        return [
            self::Pr,
            self::Rfq,
            self::Quotations,
            self::Selection,
            self::Po,
            self::Invoice,
        ];
    }

    public function index(): int
    {
        return array_search($this, self::ordered(), true);
    }
}
