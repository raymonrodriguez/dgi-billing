<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Software Provider Information (Your SaaS)
    |--------------------------------------------------------------------------
    | This information identifies you as the certified software provider
    | before the DGII.
    */
    'provider' => [
        'name' => env('DGII_PROVIDER_NAME', 'RS code capital tecnologies'),
        'rnc' => env('DGII_PROVIDER_RNC', '000000000'),
        'software_id' => env('DGII_SOFTWARE_ID', 'SaaS-001'),
        'version' => env('DGII_SOFTWARE_VERSION', '1.0.0'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Environment
    |--------------------------------------------------------------------------
    */
    'environment' => env('DGII_ENVIRONMENT', 'testecf'),
];
