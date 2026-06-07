@include('procurement.purchase-orders.print._styles')

<style>
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

    .pr-signatures-procurement {
        margin-top: 4px;
    }

    @media print {
        .pr-items-table th,
        .pr-items-table td {
            font-size: 9px;
            padding: 2px 3px;
        }
    }
</style>
