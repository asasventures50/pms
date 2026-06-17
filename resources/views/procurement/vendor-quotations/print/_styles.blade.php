<style>
    body.po-print-body {
        margin: 0;
        padding: 0;
        font-family: Arial, Helvetica, Calibri, sans-serif;
        font-size: 12px;
        color: #0f172a;
        background: #e2e8f0;
        -webkit-font-smoothing: antialiased;
    }

    .vq-print-page {
        max-width: 72rem;
        margin: 0 auto;
        padding: 16px;
        box-sizing: border-box;
    }

    .vq-document {
        max-width: 72rem;
        margin: 0 auto;
        border: 2px solid #0f172a;
        background: #fff;
        padding: 16px 24px;
        color: #0f172a;
        box-sizing: border-box;
    }

    .vq-document > table {
        width: 100%;
        border-collapse: collapse;
        border: 2px solid #0f172a;
        color: #0f172a;
    }

    .vq-document > table td {
        border: 1px solid #0f172a;
        vertical-align: middle;
    }

    .vq-document > table td:first-child {
        width: 22%;
        min-height: 5rem;
        padding: 8px;
        text-align: center;
    }

    .vq-document > table td:first-child img {
        max-height: 64px;
        max-width: 100%;
        object-fit: contain;
        border: none;
    }

    .vq-document > table td:first-child .text-xs {
        font-size: 11px;
        font-weight: 700;
        line-height: 1.25;
    }

    .vq-document > table td:last-child {
        padding: 16px;
        text-align: center;
    }

    .vq-document > table p:first-child {
        margin: 0;
        font-size: 20px;
        font-weight: 700;
        letter-spacing: -0.025em;
    }

    .vq-document > table p:last-child {
        margin: 4px 0 0;
        font-size: 11px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #475569;
    }

    .vq-document section {
        margin-top: 24px;
        font-size: 13px;
    }

    .vq-document section:first-of-type {
        margin-top: 16px;
    }

    .vq-document h3,
    .vq-document h4 {
        margin: 0;
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #0f172a;
    }

    .vq-document h4 {
        font-size: 11px;
        color: #334155;
    }

    .vq-document dl {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
        margin: 12px 0 0;
    }

    .vq-document dl > div {
        border-bottom: 1px solid #0f172a;
        padding-bottom: 4px;
    }

    .vq-document dl .sm\:col-span-2,
    .vq-document dl .sm\:col-span-3 {
        grid-column: 1 / -1;
    }

    .vq-document .sm\:grid-cols-3 {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .vq-document .lg\:grid-cols-3 {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .vq-document dt {
        font-size: 11px;
        font-weight: 500;
    }

    .vq-document dd {
        margin: 0;
    }

    .vq-document .font-mono {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    }

    .vq-document .font-semibold {
        font-weight: 600;
    }

    .vq-document .text-lg {
        font-size: 16px;
    }

    .vq-document .font-bold {
        font-weight: 700;
    }

    .vq-document .whitespace-pre-wrap {
        white-space: pre-wrap;
    }

    .vq-document .overflow-x-auto {
        overflow-x: auto;
        margin-top: 16px;
    }

    .vq-document table.min-w-full {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #0f172a;
        font-size: 11px;
    }

    .vq-document table.min-w-full th,
    .vq-document table.min-w-full td {
        border: 1px solid #0f172a;
        padding: 8px;
        vertical-align: top;
        text-align: left;
    }

    .vq-document table.min-w-full thead tr,
    .vq-document table.min-w-full tr.bg-slate-50 {
        background: #f8fafc;
    }

    .vq-document table.min-w-full th {
        font-weight: 700;
        text-transform: uppercase;
    }

    .vq-document .text-right {
        text-align: right;
    }

    .vq-document .text-center {
        text-align: center;
    }

    .vq-document .text-slate-500 {
        color: #64748b;
    }

    .vq-document .text-slate-700 {
        color: #334155;
    }

    .vq-document ul {
        margin: 12px 0 0;
        padding: 0;
        list-style: none;
    }

    .vq-document ul.space-y-2 > li + li {
        margin-top: 8px;
    }

    .vq-document ul li {
        font-size: 11px;
        line-height: 1.625;
    }

    .vq-document ul li.border-b {
        display: flex;
        flex-wrap: wrap;
        align-items: baseline;
        gap: 0 8px;
        border-bottom: 1px solid #e2e8f0;
        padding-bottom: 8px;
    }

    .vq-document ul li.flex {
        display: flex;
        align-items: flex-start;
        gap: 8px;
    }

    .vq-document .shrink-0 {
        flex-shrink: 0;
    }

    .vq-document .underline {
        text-decoration: underline;
    }

    .vq-document .border-t {
        border-top: 1px solid #0f172a;
        padding-top: 16px;
    }

    .vq-document img {
        max-height: 64px;
        max-width: 20rem;
        border: 1px solid #e2e8f0;
    }

    .vq-document .block {
        display: block;
        margin-top: 8px;
    }

    .vq-document p {
        margin: 0;
    }

    .vq-document p.mt-3 {
        margin-top: 12px;
    }

    .vq-document .text-xs {
        font-size: 11px;
    }

    .vq-document .font-medium {
        font-weight: 500;
    }

    .vq-document .uppercase {
        text-transform: uppercase;
    }

    @media print {
        @page {
            margin: 12mm;
        }

        body.po-print-body {
            background: #fff;
        }

        .print-toolbar {
            display: none !important;
        }

        .vq-print-page {
            max-width: none;
            padding: 0;
        }

        .vq-document {
            border: 2px solid #0f172a;
            box-shadow: none;
            padding: 12px 16px;
        }

        .vq-document > table,
        .vq-document section {
            break-inside: avoid-page;
            page-break-inside: avoid;
        }

        .vq-document table.min-w-full thead {
            display: table-header-group;
        }

        .vq-document table.min-w-full th,
        .vq-document table.min-w-full td {
            font-size: 9px;
            padding: 4px 3px;
        }
    }
</style>
