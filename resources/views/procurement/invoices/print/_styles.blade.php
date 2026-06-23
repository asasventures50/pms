<style>
    body.po-print-body {
        margin: 0;
        padding: 0;
        font-family: Arial, Tahoma, 'Segoe UI', sans-serif;
        font-size: 12px;
        color: #111827;
        background: #e2e8f0;
    }

    .inv-print-page {
        max-width: 210mm;
        margin: 0 auto;
        padding: 16px;
        box-sizing: border-box;
    }

    .inv-print-document {
        display: flex;
        flex-direction: column;
        min-height: calc(100vh - 32px);
    }

    .inv-print-main {
        flex: 1 0 auto;
    }

    .inv-print--rtl {
        direction: rtl;
        text-align: right;
    }

    .inv-header {
        display: grid;
        grid-template-columns: 1fr;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 14px;
        border-bottom: 1px solid #e5e7eb;
        position: relative;
        min-height: 72px;
    }

    .inv-header-logo {
        grid-column: 1;
        grid-row: 1;
        justify-self: start;
        z-index: 1;
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
        grid-column: 1;
        grid-row: 1;
        justify-self: center;
        align-self: center;
        font-size: 22px;
        font-weight: 700;
        letter-spacing: 0.02em;
        color: #111827;
        text-align: center;
        width: 100%;
        pointer-events: none;
    }

    .inv-ltr {
        direction: ltr;
        unicode-bidi: embed;
        display: inline-block;
    }

    .inv-meta-simple {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        width: 100%;
        margin-bottom: 20px;
        font-size: 12px;
    }

    .inv-meta-row {
        display: flex;
        gap: 8px;
        align-items: baseline;
    }

    .inv-meta-label {
        font-weight: 600;
        color: #4b5563;
        white-space: nowrap;
    }

    .inv-meta-value {
        white-space: nowrap;
    }

    .inv-recipient-block {
        margin-bottom: 20px;
        padding: 12px 16px;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        background: #fafafa;
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
        margin-bottom: 20px;
    }

    .inv-table-frame {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        overflow: hidden;
        background: #fff;
    }

    .inv-items-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        margin-bottom: 0;
    }

    .inv-items-table th,
    .inv-items-table td {
        border: none;
        border-bottom: 1px solid #e5e7eb;
        padding: 14px 16px;
        vertical-align: middle;
        word-wrap: break-word;
    }

    .inv-items-table thead th {
        background: #f8fafc;
        font-weight: 600;
        font-size: 11px;
        letter-spacing: 0.04em;
        color: #4b5563;
        text-align: center;
        border-bottom: 1px solid #e5e7eb;
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
        color: #6b7280;
        font-size: 11px;
    }

    .inv-cell-text {
        text-align: right;
        line-height: 1.6;
        color: #111827;
    }

    .inv-cell-money {
        text-align: center;
        direction: ltr;
        font-variant-numeric: tabular-nums;
        color: #111827;
    }

    .inv-fee-label {
        font-weight: 600;
        text-align: right;
        color: #374151;
    }

    .inv-fee-value {
        font-weight: 600;
        color: #111827;
    }

    .inv-totals-grand td {
        border-bottom: none;
        border-top: 2px solid #1f2937;
        padding-top: 16px;
        padding-bottom: 16px;
        background-color: #f3f4f6;
    }

    .inv-totals-grand .inv-fee-label {
        font-size: 13px;
        font-weight: 700;
        color: #111827;
    }

    .inv-grand-total-amount {
        font-size: 16px;
        font-weight: 700;
        color: #111827;
    }

    .inv-footer {
        flex-shrink: 0;
        margin-top: auto;
        padding-top: 12px;
        border-top: 1px solid #e5e7eb;
        text-align: center;
        font-size: 9px;
        line-height: 1.35;
        color: #6b7280;
    }

    .inv-footer-legal {
        margin-bottom: 2px;
    }

    .inv-footer-contact {
        margin-top: 4px;
        white-space: nowrap;
    }

    .inv-footer-sep {
        color: #94a3b8;
        margin-inline: 4px;
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

        .inv-print-document {
            min-height: 100vh;
        }

        .inv-header,
        .inv-recipient-block,
        .inv-table-frame,
        .inv-footer {
            break-inside: avoid-page;
            page-break-inside: avoid;
        }

        .inv-items-table thead {
            display: table-header-group;
        }

        .inv-items-table tbody tr {
            break-inside: avoid-page;
            page-break-inside: avoid;
        }

        .inv-totals-grand td {
            background-color: #f3f4f6 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }
</style>
