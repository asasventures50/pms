<?php

namespace App\Support\Procurement;

final class PurchaseOrderPrintPageCss
{
    public static function styleTag(string $footerCompanyName): string
    {
        return '<style>'.self::pageSetup($footerCompanyName).'</style>';
    }

    public static function pageSetup(string $footerCompanyName): string
    {
        $footerCompanyContent = json_encode(
            strtoupper($footerCompanyName),
            JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE
        );

        return <<<CSS
        @page {
            size: A4 portrait;
            margin: 10mm 14mm 18mm 14mm;
            background: #fff;

            @bottom-left {
                content: "Form PO";
                font-family: Arial, Helvetica, sans-serif;
                font-size: 11px;
                font-weight: bold;
                vertical-align: top;
                padding-top: 2mm;
                padding-left: 0;
                border-top: 2px solid #000;
            }

            @bottom-center {
                content: {$footerCompanyContent};
                font-family: Arial, Helvetica, sans-serif;
                font-size: 11px;
                font-weight: bold;
                text-align: center;
                vertical-align: top;
                padding-top: 2mm;
                border-top: 2px solid #000;
            }

            @bottom-right {
                content: counter(page) "/" counter(pages);
                font-family: Arial, Helvetica, sans-serif;
                font-size: 11px;
                font-weight: bold;
                text-align: right;
                vertical-align: top;
                padding-top: 2mm;
                padding-right: 0;
                border-top: 2px solid #000;
            }
        }
        CSS;
    }
}
