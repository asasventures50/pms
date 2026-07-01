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
        --comparison-criteria-width: 12%;
        table-layout: fixed;
        width: 100%;
        margin-left: auto;
        margin-right: auto;
    }

    .comparison-table th,
    .comparison-table td {
        vertical-align: middle;
        word-break: break-word;
        overflow-wrap: anywhere;
    }

    .comparison-table .comparison-criteria-cell {
        width: var(--comparison-criteria-width);
        min-width: 0;
        text-align: center;
    }

    .comparison-table .comparison-data-cell {
        width: calc((100% - var(--comparison-criteria-width)) / var(--comparison-quotation-count, 1));
        min-width: 0;
        text-align: center;
    }

    .comparison-table-wrapper {
        max-width: 100%;
        margin-left: auto;
        margin-right: auto;
    }

    .comparison-print-checkbox {
        display: inline-block;
        width: 1.125rem;
        height: 1.125rem;
        border: 1.5px solid #64748b;
        border-radius: 2px;
        background: #fff;
    }

    .comparison-signoff-select-row,
    .comparison-signoff-notes {
        display: none;
    }

    .comparison-handwritten-notes-box {
        min-height: 5rem;
        border: 1px solid #cbd5e1;
        border-radius: 0.25rem;
        background: #fff;
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
            width: var(--comparison-criteria-width) !important;
            min-width: 0 !important;
        }

        .comparison-table .comparison-data-cell {
            width: calc((100% - var(--comparison-criteria-width)) / var(--comparison-quotation-count, 1)) !important;
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
            text-align: center !important;
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
            display: none !important;
        }

        .comparison-screen-select-row {
            display: none !important;
        }

        .comparison-signoff-select-row {
            display: table-row !important;
        }

        .comparison-signoff-select-row td {
            background-color: #f8fafc !important;
            padding-top: 10px !important;
            padding-bottom: 10px !important;
        }

        .comparison-signoff-notes {
            display: block !important;
            break-inside: avoid-page;
            page-break-inside: avoid;
            border-color: #e2e8f0 !important;
            background: #f8fafc !important;
        }

        .comparison-handwritten-notes-box {
            min-height: 4.5rem !important;
            border-color: #cbd5e1 !important;
        }

        .comparison-print-checkbox {
            width: 14px !important;
            height: 14px !important;
        }
    }
</style>
