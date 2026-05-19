<?php

return [

    /*
    |--------------------------------------------------------------------------
    | RFQ form — extended fields
    |--------------------------------------------------------------------------
    |
    | When false, vendor, quotation, payment, and status blocks are hidden on
    | create/edit. Set RFQ_SHOW_EXTENDED_FIELDS=true in .env to show them.
    |
    */
    'rfq' => [
        'show_extended_form_fields' => (bool) env('RFQ_SHOW_EXTENDED_FIELDS', false),
    ],

];
