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
        padding: 12px;
        box-sizing: border-box;
    }

    .inv-print-document {
        display: flex;
        flex-direction: column;
    }

    .inv-print-main {
        flex: 0 1 auto;
    }

    .inv-print--rtl {
        direction: rtl;
        text-align: right;
    }

    .inv-header {
        display: grid;
        grid-template-columns: 1fr;
        align-items: center;
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 1px solid #e5e7eb;
        position: relative;
        min-height: 60px;
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
        margin-bottom: 12px;
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

    .inv-project-block {
        margin-bottom: 8px;
        padding: 8px 12px;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        background: #fafafa;
        font-size: 13px;
    }

    .inv-project-label {
        font-weight: bold;
        margin-inline-end: 8px;
    }

    .inv-project-name {
        font-weight: 600;
    }

    .inv-recipient-block {
        margin-bottom: 12px;
        padding: 8px 12px;
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
        margin-bottom: 12px;
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
        padding: 8px 10px;
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
        width: 36px;
    }

    .inv-items-table .col-project {
        width: 110px;
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
        width: 100px;
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

    .inv-cell-project {
        text-align: right;
        line-height: 1.5;
        font-size: 11px;
        color: #374151;
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
        white-space: nowrap;
    }

    .inv-totals-grand td {
        border-bottom: none;
        padding-top: 10px;
        padding-bottom: 10px;
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
        white-space: nowrap;
    }

    .inv-notes-block {
        margin-bottom: 10px;
        padding: 8px 12px;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        background: #fafafa;
        height: auto;
        min-height: 0;
        width: 100%;
        box-sizing: border-box;
    }

    .inv-notes-title {
        font-weight: 700;
        font-size: 13px;
        color: #111827;
        margin-bottom: 4px;
    }

    .inv-notes-list {
        margin: 0;
        padding: 0 20px 0 0;
        list-style-type: disc;
    }

    .inv-notes-list li {
        margin-bottom: 4px;
        line-height: 1.6;
        color: #374151;
    }

    .inv-notes-list li:last-child {
        margin-bottom: 0;
    }

    .inv-bank-block {
        margin-bottom: 10px;
        padding: 8px 12px;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        background: #fafafa;
        width: 100%;
        box-sizing: border-box;
        text-align: right;
    }

    .inv-bank-title {
        font-weight: 700;
        font-size: 15px;
        color: #111827;
        margin-bottom: 4px;
        line-height: 1.3;
    }

    .inv-bank-line {
        font-size: 12px;
        line-height: 1.45;
        color: #374151;
    }

    .inv-bank-line strong {
        font-weight: 700;
        color: #111827;
    }

    .inv-pre-footer {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
        width: 100%;
        margin-top: 0;
        margin-bottom: 0;
        padding: 8px 0 4px;
        border-top: 1px solid #e5e7eb;
        align-items: start;
        break-inside: avoid-page;
        page-break-inside: avoid;
    }

    .inv-print-bottom {
        flex-shrink: 0;
        width: 100%;
        margin-top: 12px;
    }

    .inv-pre-footer-col {
        min-width: 0;
    }

    .inv-pre-footer-signature {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .inv-pre-footer-signature--receive {
        justify-self: start;
    }

    .inv-pre-footer-signature--accounts {
        justify-self: center;
    }

    .inv-pre-footer-signature--general {
        justify-self: end;
    }

    .inv-signature-label {
        font-weight: 700;
        font-size: 13px;
        color: #111827;
        line-height: 1.4;
        max-width: 12rem;
    }

    .inv-signature-name {
        font-size: 14px;
        font-weight: 600;
        color: #111827;
        margin-top: 4px;
        margin-bottom: 0;
        line-height: 1.3;
    }

    .inv-signature-line {
        width: 12rem;
        max-width: 100%;
        margin-top: 4px;
        flex-shrink: 0;
        border-top: 2px solid #111827;
    }

    .inv-signature-caption {
        width: 12rem;
        max-width: 100%;
        margin-top: 10px;
        flex-shrink: 0;
        font-size: 11px;
        color: #374151;
        text-align: right;
        padding-inline-end: 6px;
        line-height: 3;
    }

    .inv-signature-pad {
        width: 12rem;
        max-width: 100%;
        height: 2.6em;
        margin-top: 4px;
        flex-shrink: 0;
        box-sizing: border-box;
    }

    .inv-footer {
        flex-shrink: 0;
        padding: 8px 16px 10px;
        border-top: 1px solid #e5e7eb;
        text-align: center;
        font-size: 9px;
        line-height: 1.45;
        color: #6b7280;
        background: #fff;
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
            padding-bottom: 0;
        }

        .inv-print-document {
            display: block;
            min-height: 0;
            padding-bottom: 0;
        }

        .inv-print-main {
            display: block;
            padding-bottom: 0;
        }

        .inv-header,
        .inv-project-block,
        .inv-recipient-block,
        .inv-notes-block,
        .inv-bank-block,
        .inv-pre-footer {
            break-inside: avoid-page;
            page-break-inside: avoid;
        }

        .inv-table-frame {
            overflow: visible;
            break-inside: auto;
            page-break-inside: auto;
        }

        .inv-items-table thead {
            display: table-header-group;
        }

        .inv-items-table tbody tr {
            break-inside: avoid-page;
            page-break-inside: avoid;
        }

        .inv-totals-grand {
            break-inside: avoid-page;
            page-break-inside: avoid;
        }

        .inv-print-bottom {
            position: static;
            width: 100%;
            background: #fff;
            padding: 0;
            box-sizing: border-box;
            margin-top: 12px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .inv-pre-footer {
            grid-template-columns: repeat(3, minmax(0, 1fr));
            border-top: none;
            margin-top: 0;
            margin-bottom: 0;
            padding: 6px 0 4px;
            break-inside: avoid-page;
            page-break-inside: avoid;
        }

        .inv-footer {
            position: static;
            margin: 0;
            padding: 6px 0 4px;
            border-top: 1px solid #e5e7eb;
            background: #fff;
        }

        .inv-totals-grand td {
            background-color: #f3f4f6 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }
</style>
