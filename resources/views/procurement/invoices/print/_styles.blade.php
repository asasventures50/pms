<style>
    body.po-print-body {
        margin: 0;
        padding: 0;
        font-family: Arial, Tahoma, 'Segoe UI', sans-serif;
        font-size: 12px;
        color: #000;
        background: #e2e8f0;
    }

    .inv-print-page {
        max-width: 210mm;
        margin: 0 auto;
        padding: 16px;
        box-sizing: border-box;
    }

    .inv-print--rtl {
        direction: rtl;
        text-align: right;
    }

    .inv-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid #ccc;
    }

    .inv-header-logo {
        flex-shrink: 0;
    }

    .inv-logo-img {
        max-height: 72px;
        max-width: 180px;
        object-fit: contain;
    }

    .inv-logo-fallback {
        font-size: 14px;
        font-weight: bold;
        line-height: 1.3;
        text-align: center;
    }

    .inv-header-title {
        font-size: 22px;
        font-weight: bold;
        flex: 1;
        text-align: center;
    }

    .inv-ltr {
        direction: ltr;
        unicode-bidi: embed;
        display: inline-block;
    }

    .inv-meta-simple {
        display: flex;
        flex-wrap: wrap;
        gap: 12px 32px;
        margin-bottom: 20px;
        font-size: 12px;
    }

    .inv-meta-row {
        display: flex;
        gap: 8px;
        align-items: baseline;
    }

    .inv-meta-label {
        font-weight: bold;
        white-space: nowrap;
    }

    .inv-meta-value {
        flex: 1;
    }

    .inv-recipient-block {
        margin-bottom: 16px;
        padding: 10px 12px;
        border: 1px solid #333;
        font-size: 13px;
    }

    .inv-recipient-label {
        font-weight: bold;
        margin-inline-end: 8px;
    }

    .inv-recipient-name {
        font-weight: 600;
    }

    .inv-tables-block {
        margin-bottom: 16px;
    }

    .inv-items-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        margin-bottom: 0;
    }

    .inv-items-table th,
    .inv-items-table td {
        border: 1px solid #333;
        padding: 8px 10px;
        vertical-align: middle;
        word-wrap: break-word;
    }

    .inv-items-table th {
        background: #f1f5f9;
        font-weight: bold;
        font-size: 11px;
        text-align: center;
    }

    .inv-items-table .col-num {
        width: 40px;
    }

    .inv-items-table .col-desc {
        width: auto;
    }

    .inv-items-table .col-qty {
        width: 70px;
    }

    .inv-items-table .col-unit {
        width: 60px;
    }

    .inv-items-table .col-price {
        width: 90px;
    }

    .inv-items-table .col-total {
        width: 90px;
    }

    .inv-cell-num {
        text-align: center;
    }

    .inv-cell-text {
        text-align: right;
        line-height: 1.5;
    }

    .inv-cell-money {
        text-align: center;
        direction: ltr;
        font-variant-numeric: tabular-nums;
    }

    .inv-totals-wrap {
        display: flex;
        justify-content: flex-start;
        margin-top: 0;
        line-height: 0;
    }

    .inv-totals-table {
        width: auto;
        min-width: 16rem;
        max-width: 22rem;
        border-collapse: collapse;
        margin-top: -1px;
    }

    .inv-totals-table td {
        border: 1px solid #333;
        padding: 7px 10px;
        vertical-align: middle;
        line-height: normal;
    }

    .inv-totals-table tr:first-child td {
        border-top: none;
    }

    .inv-totals-label {
        font-weight: bold;
        text-align: right;
        white-space: nowrap;
    }

    .inv-totals-value {
        text-align: left;
        direction: ltr;
        font-weight: bold;
        white-space: nowrap;
        font-variant-numeric: tabular-nums;
    }

    .inv-totals-grand .inv-totals-label,
    .inv-totals-grand .inv-totals-value {
        font-size: 13px;
        background: #f8fafc;
    }

    .inv-footer {
        margin-top: 28px;
        padding-top: 14px;
        border-top: 1px solid #ccc;
        text-align: center;
        font-size: 11px;
        line-height: 1.7;
        color: #1e293b;
    }

    @media print {
        body.po-print-body {
            background: #fff;
        }

        .print-toolbar {
            display: none !important;
        }

        .inv-print-page {
            max-width: none;
            padding: 0;
        }

        .inv-header,
        .inv-recipient-block,
        .inv-footer {
            break-inside: avoid-page;
            page-break-inside: avoid;
        }

        .inv-items-table thead {
            display: table-header-group;
        }

        .inv-items-table tbody tr,
        .inv-totals-wrap,
        .inv-totals-table tr {
            break-inside: avoid-page;
            page-break-inside: avoid;
        }
    }
</style>
