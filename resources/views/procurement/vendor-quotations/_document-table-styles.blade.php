<style>
    .vq-document .vq-table-scroll {
        overflow-x: auto;
        margin-top: 16px;
        max-width: 100%;
    }

    .vq-document .vq-lines-table,
    .vq-document .vq-request-lines-table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #0f172a;
        table-layout: fixed;
    }

    .vq-document .vq-lines-table th,
    .vq-document .vq-lines-table td,
    .vq-document .vq-request-lines-table th,
    .vq-document .vq-request-lines-table td {
        border: 1px solid #0f172a;
        padding: 6px 4px;
        vertical-align: top;
        text-align: left;
        word-break: break-word;
        overflow-wrap: anywhere;
        hyphens: auto;
        line-height: 1.25;
    }

    .vq-document .vq-lines-table th,
    .vq-document .vq-request-lines-table th {
        font-weight: 700;
        text-transform: uppercase;
        font-size: 10px;
        vertical-align: bottom;
    }

    .vq-document .vq-lines-table .vq-cell-num,
    .vq-document .vq-request-lines-table .vq-cell-num {
        text-align: right;
        white-space: nowrap;
        word-break: normal;
        overflow-wrap: normal;
    }

    .vq-document .vq-lines-table .vq-cell-index {
        text-align: center;
        white-space: nowrap;
        width: 2.5%;
    }

    .vq-document .vq-lines-table thead tr,
    .vq-document .vq-request-lines-table thead tr {
        background: #f8fafc;
    }

    @media print {
        @page {
            size: landscape;
            margin: 10mm;
        }

        .vq-document .vq-table-scroll {
            overflow: visible;
        }

        .vq-document .vq-lines-table th,
        .vq-document .vq-lines-table td,
        .vq-document .vq-request-lines-table th,
        .vq-document .vq-request-lines-table td {
            font-size: 7px;
            padding: 2px 2px;
            line-height: 1.2;
        }

        .vq-document .vq-lines-table th,
        .vq-document .vq-request-lines-table th {
            font-size: 6.5px;
        }

        .vq-document .vq-lines-table thead {
            display: table-header-group;
        }

        .vq-document .vq-empty-col,
        .vq-document .vq-empty-field,
        .vq-document .vq-empty-row,
        .vq-document .vq-empty-section {
            display: none !important;
        }
    }
</style>
