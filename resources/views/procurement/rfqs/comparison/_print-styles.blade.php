<style>
    .comparison-document {
        --comparison-header-bg: #f8fafc;
        --comparison-header-selected-bg: #f1f5f9;
        --comparison-header-text: #1e293b;
        --comparison-header-muted: #64748b;
        --comparison-header-border: #e2e8f0;
        max-width: none;
    }

    .comparison-table {
        table-layout: fixed;
        width: 100%;
    }

    .comparison-table th,
    .comparison-table td {
        vertical-align: middle;
        word-break: break-word;
        overflow-wrap: anywhere;
    }

    .comparison-table .comparison-criteria-cell {
        width: 13%;
        min-width: 0;
        text-align: left;
    }

    .comparison-table .comparison-data-cell {
        min-width: 0;
        text-align: center;
    }

    .comparison-table-wrapper {
        max-width: 100%;
    }

    .comparison-table .comparison-header-accent,
    .comparison-table .comparison-header-accent th {
        background-color: var(--comparison-header-bg) !important;
        color: var(--comparison-header-text) !important;
        border-color: var(--comparison-header-border) !important;
    }

    .comparison-table .comparison-col-selected {
        background-color: var(--comparison-header-selected-bg) !important;
    }

    @media print {
        @page {
            size: landscape;
            margin: 10mm;
        }

        html,
        body {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .comparison-document {
            border: 2px solid #0f172a !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            padding: 12px 16px !important;
            max-width: none !important;
            width: 100% !important;
        }

        .comparison-table-wrapper {
            overflow: visible !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            border: none !important;
        }

        .comparison-table {
            width: 100% !important;
            table-layout: fixed !important;
            font-size: 9px !important;
        }

        .comparison-table th,
        .comparison-table td {
            word-break: break-word !important;
            overflow-wrap: anywhere !important;
        }

        .comparison-table .comparison-criteria-cell {
            width: 12% !important;
            min-width: 0 !important;
        }

        .comparison-table .comparison-data-cell {
            min-width: 0 !important;
        }

        .comparison-table thead {
            display: table-row-group;
        }

        .comparison-table th,
        .comparison-table td {
            padding: 5px 6px !important;
        }

        .comparison-table .comparison-criteria-cell {
            text-align: left !important;
            min-width: 0 !important;
        }

        .comparison-table .comparison-data-cell {
            text-align: center !important;
        }

        .comparison-table .sticky {
            position: static !important;
        }

        .comparison-table .comparison-header-accent,
        .comparison-table .comparison-header-accent th {
            background-color: #f8fafc !important;
            color: #1e293b !important;
            border-color: #e2e8f0 !important;
        }

        .comparison-table .comparison-col-selected {
            background-color: #f1f5f9 !important;
        }

        .comparison-table .comparison-line-header td {
            background-color: #f8fafc !important;
        }

        .comparison-table .comparison-grand-total-row td {
            background-color: #f1f5f9 !important;
        }

        .comparison-table .comparison-lowest-total {
            background-color: #ecfdf5 !important;
            color: #064e3b !important;
        }

        .comparison-table .comparison-badge-lowest {
            background-color: #6ee7b7 !important;
            color: #022c22 !important;
        }

        .comparison-table .comparison-badge-selected {
            background-color: #fff !important;
            color: #9a3412 !important;
        }

        .comparison-supporting-docs {
            break-inside: avoid-page;
            page-break-inside: avoid;
        }

        .comparison-supporting-docs a {
            color: #1d4ed8 !important;
            text-decoration: underline !important;
        }
    }
</style>
