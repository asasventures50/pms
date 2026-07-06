<?php

namespace App\Support;

final class PrintPageNumberCss
{
    public static function styleTag(
        string $size = 'A4 portrait',
        string $marginTop = '10mm',
        string $marginRight = '10mm',
        string $marginBottom = '16mm',
        string $marginLeft = '10mm',
    ): string {
        return '<style>'.self::pageSetup($size, $marginTop, $marginRight, $marginBottom, $marginLeft).'</style>';
    }

    public static function pageSetup(
        string $size = 'A4 portrait',
        string $marginTop = '10mm',
        string $marginRight = '10mm',
        string $marginBottom = '16mm',
        string $marginLeft = '10mm',
    ): string {
        return <<<CSS
        @page {
            size: {$size};
            margin: {$marginTop} {$marginRight} {$marginBottom} {$marginLeft};
            background: #fff;

            @bottom-right {
                content: counter(page) "/" counter(pages);
                font-family: Arial, Helvetica, sans-serif;
                font-size: 11px;
                font-weight: bold;
                text-align: right;
                vertical-align: top;
                padding-top: 2mm;
                padding-right: 0;
            }
        }
        CSS;
    }
}
