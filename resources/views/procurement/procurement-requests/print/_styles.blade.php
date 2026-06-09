@include('procurement.purchase-orders.print._styles')

<style>
    .pr-print--rtl {
        direction: rtl;
        text-align: right;
    }

    .pr-print--rtl .po-header-title,
    .pr-print--rtl .po-header-dept,
    .pr-print--rtl .po-section-title,
    .pr-print--rtl .po-form-label,
    .pr-print--rtl .po-field-label,
    .pr-print--rtl .pr-print-list {
        text-align: right;
    }

    .pr-print--rtl .pr-print-list {
        padding-right: 18px;
        padding-left: 0;
    }

    .pr-print--rtl .po-items-table th,
    .pr-print--rtl .po-items-table td {
        text-align: right;
    }

    .pr-print--rtl .po-cell-num {
        text-align: left;
    }

    .pr-print--rtl .pr-cell-wrap {
        text-align: right;
    }

    .pr-print-compact .po-form-line {
        min-height: 0;
        line-height: 1.35;
    }

    .pr-print-compact .po-form-group {
        margin-bottom: 4px;
    }

    .pr-print-compact .po-grid-2 {
        margin-bottom: 10px;
    }

    .pr-print-compact .po-field-value {
        min-height: 0;
        padding: 2px 6px 4px;
    }

    .pr-items-table {
        table-layout: auto;
        width: 100%;
    }

    .pr-items-table th,
    .pr-items-table td {
        min-height: 0;
        height: auto;
        padding: 3px 4px;
        vertical-align: top;
    }

    .pr-items-table .pr-empty-table {
        text-align: center;
        padding: 8px;
        color: #64748b;
    }

    .pr-cell-stack,
    .pr-cell-scope {
        white-space: pre-line;
        word-break: normal;
        overflow-wrap: normal;
    }

    .pr-cell-wrap {
        text-align: left;
        white-space: pre-wrap;
        word-break: normal;
        overflow-wrap: break-word;
    }

    .pr-cell-scope {
        text-align: center;
    }

    .pr-cell-delivery {
        white-space: nowrap;
    }

    .pr-cell-documents {
        text-align: left;
    }

    .pr-doc-link {
        color: #1d4ed8;
        text-decoration: underline;
        word-break: break-word;
    }

    .pr-doc-link:hover {
        color: #1e3a8a;
    }

    @media print {
        .pr-doc-link {
            color: #000;
            text-decoration: underline;
        }
    }

    .pr-print-closing-block {
        margin-top: 4px;
    }

    .pr-closing-nda {
        margin: 0 0 6px;
        font-size: 12px;
    }

    .pr-approvals-table {
        margin-bottom: 0;
    }

    .pr-approvals-table col.pr-approvals-col-role {
        width: 22%;
    }

    .pr-approvals-table col.pr-approvals-col-name {
        width: 28%;
    }

    .pr-approvals-table col.pr-approvals-col-signature {
        width: 32%;
    }

    .pr-approvals-table col.pr-approvals-col-date {
        width: 18%;
    }

    .pr-approvals-table .pr-approvals-role {
        font-weight: bold;
        text-align: left;
    }

    .pr-approvals-table .pr-approvals-name,
    .pr-approvals-table .pr-approvals-date {
        text-align: left;
    }

    .pr-approvals-table .pr-approvals-signature {
        min-height: 32px;
        text-align: left;
    }

    .pr-approvals-table .pr-approvals-row td {
        vertical-align: middle;
    }

    .pr-project-group-heading {
        font-weight: 700;
        background: #f1f5f9;
        text-align: left;
    }

    .pr-print-list {
        margin: 0 0 10px;
        padding-left: 18px;
        font-size: 12px;
    }

    .pr-print-muted {
        color: #64748b;
    }

    .pr-form-option-group .po-form-label {
        font-weight: normal;
    }

    .pr-form-option-line {
        white-space: normal;
    }

    .pr-form-option-required {
        color: #000;
    }

    .pr-form-option-hint {
        margin: -2px 0 6px;
        font-size: 10px;
        color: #475569;
        line-height: 1.35;
    }

    .pr-print--rtl .pr-form-option-hint {
        text-align: right;
    }

    .pr-compact-table th,
    .pr-compact-table td {
        font-size: 10px;
    }

    @media print {
        .pr-print-compact.po-wrapper,
        .po-wrapper.pr-print-compact {
            -webkit-box-decoration-break: clone;
            box-decoration-break: clone;
        }

        .pr-print-closing-block {
            break-inside: avoid-page;
            page-break-inside: avoid;
        }

        .pr-approvals-table {
            break-inside: avoid-page;
            page-break-inside: avoid;
        }

        .pr-approvals-table .pr-approvals-row {
            break-inside: avoid-page;
            page-break-inside: avoid;
        }

        .pr-items-table th,
        .pr-items-table td {
            font-size: 9px;
            padding: 2px 3px;
        }
    }
</style>
