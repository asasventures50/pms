<style>
    body.po-print-body {
        margin: 0;
        padding: 0;
        font-family: Arial, Helvetica, Calibri, sans-serif;
        font-size: 12px;
        color: #000;
        background: #e2e8f0;
    }

    @media print {
        body.po-print-body {
            background: #fff;
        }

        .print-toolbar {
            display: none !important;
        }

        .po-print-page {
            padding: 0 !important;
            max-width: none !important;
            width: 100%;
            margin: 0 auto;
        }

        .po-wrapper {
            width: 100%;
            max-width: 100%;
        }

        .po-footer--screen-only {
            display: none !important;
        }

        .po-field-block,
        .po-form-group--row {
            break-inside: avoid-page;
            page-break-inside: avoid;
        }

        .po-terms-list li {
            break-inside: avoid-page;
            page-break-inside: avoid;
        }

        .po-signatures {
            break-inside: avoid-page;
            page-break-inside: avoid;
        }
    }

    .po-wrapper {
        border: 2px solid #000;
        padding: 12px;
        box-sizing: border-box;
        background: #fff;
    }

    .po-header-table {
        width: 100%;
        border-collapse: collapse;
        border: 2px solid #000;
        margin-bottom: 12px;
        table-layout: fixed;
    }

    .po-header-table td {
        border: 1px solid #000;
        padding: 8px;
        text-align: center;
        vertical-align: middle;
    }

    .po-header-logo {
        width: 25%;
    }

    .po-header-title {
        width: 50%;
        font-size: 20px;
        font-weight: bold;
    }

    .po-header-dept {
        width: 25%;
        font-weight: bold;
        line-height: 1.4;
    }

    .po-logo-img {
        max-height: 50px;
        max-width: 100%;
        object-fit: contain;
    }

    .po-logo-fallback {
        color: #d2691e;
        font-size: 13px;
        font-weight: bold;
        line-height: 1.3;
    }

    .po-company-info {
        margin-bottom: 14px;
        line-height: 1.55;
        font-size: 11px;
        font-weight: bold;
    }

    .po-company-name {
        font-size: 14px;
        margin-bottom: 2px;
    }

    .po-section-title {
        font-weight: bold;
        font-size: 13px;
        margin-bottom: 6px;
    }

    .po-grid-2 {
        display: table;
        width: 100%;
        margin-bottom: 14px;
        table-layout: fixed;
    }

    .po-grid-col {
        display: table-cell;
        width: 50%;
        vertical-align: top;
        padding-right: 8px;
    }

    .po-grid-col:last-child {
        padding-right: 0;
        padding-left: 8px;
    }

    .po-form-group {
        margin-bottom: 6px;
        line-height: 1.5;
    }

    .po-form-label {
        display: inline-block;
        font-weight: normal;
        vertical-align: bottom;
    }

    .po-form-line {
        display: inline-block;
        border-bottom: 1px solid #000;
        min-height: 16px;
        vertical-align: bottom;
        padding: 0 2px 1px;
        word-break: break-word;
    }

    .po-order-left .po-form-label {
        width: 95px;
        text-align: right;
        margin-right: 4px;
    }

    .po-order-left .po-form-line {
        width: calc(100% - 105px);
        max-width: 220px;
    }

    .po-order-right .po-form-label {
        width: 88px;
    }

    .po-order-right .po-form-line {
        width: calc(100% - 96px);
        max-width: 200px;
    }

    .po-parties .po-form-label {
        width: 118px;
    }

    .po-parties .po-form-line {
        width: calc(100% - 124px);
    }

    .po-items-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 0;
        border: 2px solid #000;
    }

    .po-items-table th,
    .po-items-table td {
        border: 1px solid #000;
        padding: 6px 4px;
        text-align: center;
        vertical-align: middle;
    }

    .po-items-table th {
        font-weight: bold;
        font-size: 11px;
        line-height: 1.25;
    }

    .po-items-table td {
        min-height: 25px;
        height: 25px;
        font-size: 11px;
    }

    .po-items-table .col-item {
        width: 8%;
    }

    .po-items-table .col-desc {
        width: 28%;
    }

    .po-items-table .col-scope {
        width: 18%;
    }

    .po-totals-wrap {
        margin-bottom: 14px;
    }

    .po-totals-table {
        width: 42%;
        margin-left: auto;
        border-collapse: collapse;
        border: 2px solid #000;
        border-top: none;
    }

    .po-totals-table td {
        border: 1px solid #000;
        padding: 5px 8px;
        font-weight: bold;
        font-size: 11px;
    }

    .po-totals-label {
        text-align: right;
        width: 58%;
    }

    .po-totals-value {
        text-align: center;
        width: 42%;
    }

    .po-form-label--wide {
        width: 200px;
    }

    .po-form-line--flex {
        width: auto;
        max-width: none;
        flex: 1;
        min-width: 0;
    }

    .po-form-group--row {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        gap: 4px;
        width: 100%;
    }

    .po-field-block {
        box-sizing: border-box;
        width: 100%;
        margin-bottom: 10px;
    }

    .po-field-label {
        font-weight: bold;
        font-size: 13px;
        margin-bottom: 4px;
    }

    .po-field-value {
        display: block;
        box-sizing: border-box;
        width: 100%;
        border-bottom: 1px solid #000;
        min-height: 22px;
        padding: 4px 10px 6px;
        line-height: 1.45;
        white-space: pre-wrap;
        word-break: break-word;
        overflow-wrap: anywhere;
    }

    .po-field-value--empty {
        min-height: 20px;
    }

    .po-terms-block {
        box-sizing: border-box;
        width: 100%;
        margin-top: 6px;
        padding: 0 4px;
        font-size: 9px;
    }

    .po-terms-block--rtl {
        direction: rtl;
        text-align: right;
        unicode-bidi: embed;
        padding-right: 24px;
        padding-left: 12px;
        font-family: 'Segoe UI', Tahoma, Arial, sans-serif;
    }

    .po-terms-block--rtl .po-field-label {
        text-align: right;
    }

    .po-terms-list {
        margin: 0;
        padding: 0 0 0 1rem;
        list-style: disc;
        list-style-position: outside;
    }

    .po-terms-block--rtl .po-terms-list {
        padding-right: 1.25rem;
        padding-left: 0;
        margin: 0 0 0 0;
        list-style-position: outside;
    }

    .po-terms-list li {
        margin-bottom: 6px;
        line-height: 1.35;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }

    .po-term-key {
        font-weight: bold;
    }

    .po-terms-block .po-field-label {
        font-size: 9px;
        margin-bottom: 3px;
    }

    .po-terms-block--rtl .po-section-title {
        text-align: right;
    }

    .po-signatures {
        display: table;
        width: 100%;
        margin-top: 16px;
        margin-bottom: 12px;
    }

    .po-signature-col {
        display: table-cell;
        width: 50%;
        vertical-align: top;
        padding-right: 12px;
    }

    .po-signature-col:last-child {
        padding-right: 0;
        padding-left: 12px;
    }

    .po-signature-row {
        margin-bottom: 10px;
    }

    .po-signature-row .po-form-label {
        width: 72px;
        font-weight: bold;
    }

    .po-signature-row .po-form-line {
        width: calc(100% - 80px);
        max-width: 240px;
    }

    .po-footer-cell {
        display: table-cell;
        width: 33.33%;
        vertical-align: middle;
    }

    .po-footer-left {
        text-align: left;
        padding-left: 6px;
    }

    .po-footer-center {
        text-align: center;
    }

    .po-footer-right {
        text-align: right;
        padding-right: 6px;
    }

    .po-footer {
        width: 100%;
        border-top: 2px solid #000;
        border-bottom: 2px solid #000;
        display: table;
        margin-top: 8px;
        padding: 5px 0;
        font-size: 11px;
        font-weight: bold;
        box-sizing: border-box;
    }

    .po-footer--screen-only {
        margin-top: 16px;
    }
</style>
