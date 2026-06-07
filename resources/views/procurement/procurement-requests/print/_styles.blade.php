@include('procurement.purchase-orders.print._styles')

<style>
    .pr-items-table col.col-line,
    .pr-items-table .col-line {
        width: 9%;
    }

    .pr-items-table col.col-project,
    .pr-items-table .col-project {
        width: 13%;
    }

    .pr-items-table col.col-category,
    .pr-items-table .col-category {
        width: 12%;
    }

    .pr-items-table col.col-scope,
    .pr-items-table .col-scope {
        width: 11%;
    }

    .pr-items-table col.col-desc,
    .pr-items-table .col-desc {
        width: 15%;
    }

    .pr-items-table col.col-sow,
    .pr-items-table .col-sow {
        width: 30%;
    }

    .pr-items-table col.col-unit,
    .pr-items-table .col-unit {
        width: 5%;
    }

    .pr-items-table col.col-qty,
    .pr-items-table .col-qty {
        width: 5%;
    }

    .pr-cell-stack,
    .pr-cell-scope {
        white-space: pre-line;
        word-break: normal;
        overflow-wrap: normal;
    }

    .pr-cell-scope {
        text-align: center;
        vertical-align: middle;
    }

    .pr-line-details {
        margin-top: 10px;
        margin-bottom: 12px;
        padding: 0 2px;
    }

    .pr-line-details-title {
        font-weight: bold;
        font-size: 11px;
        margin-bottom: 6px;
    }

    .pr-line-details .po-field-block {
        margin-bottom: 8px;
    }

    .pr-line-details .po-field-label {
        font-size: 10px;
    }

    .pr-line-details .po-field-value {
        font-size: 10px;
        min-height: 18px;
    }

    .pr-line-delivery-meta {
        font-size: 10px;
        line-height: 1.5;
        padding: 2px 0;
    }

    .pr-line-meta-label {
        font-weight: bold;
    }

    .pr-line-meta-sep {
        margin: 0 6px;
        color: #64748b;
    }

    .pr-line-meta-note {
        margin-left: 4px;
        color: #475569;
    }

    .pr-signatures-procurement {
        margin-top: 4px;
    }

    @media print {
        .pr-line-details {
            break-inside: avoid-page;
            page-break-inside: avoid;
        }
    }
</style>
