<?php

namespace App\Enums\Procurement\ProcurementRequests;

enum ProcurementTimelineActivity: string
{
    case RfqIssuance = 'rfq_issuance';
    case QuotationSubmission = 'quotation_submission';
    case TechnicalEvaluation = 'technical_evaluation';
    case CommercialEvaluation = 'commercial_evaluation';
    case Negotiation = 'negotiation';
    case ApprovalProcess = 'approval_process';
    case ContractAward = 'contract_award';
    case PoIssuance = 'po_issuance';

    public function label(): string
    {
        return match ($this) {
            self::RfqIssuance => 'RFQ Issuance',
            self::QuotationSubmission => 'Quotation Submission',
            self::TechnicalEvaluation => 'Technical Evaluation',
            self::CommercialEvaluation => 'Commercial Evaluation',
            self::Negotiation => 'Negotiation',
            self::ApprovalProcess => 'Approval Process',
            self::ContractAward => 'Contract Award',
            self::PoIssuance => 'PO Issuance',
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
