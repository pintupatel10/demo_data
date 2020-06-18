<?php

return [

    /**
     * The SMS service to use. twilio or plivo
     */
    'driver' => env('SMS_DRIVER','twilio'),

    /**
     * Plivo settings
     */
    'plivo' => [
        'token' => env('PLIVO_AUTH_TOKEN'),
        'user'  => env('PLIVO_AUTH_ID'),
        'from'  => env('PLIVO_FROM',null), //Default from phone number
    ], 

    /**
     * Twilio settings
     */
    'twilio' => [
        'token' => env('26ab75f3bc09979562735d3165213aaa'),
        'user'  => env('AC84d6bdeea9caa5c231366d5f6705eb51'),
        'from'  => env('+14388004695',null), //Default from phone number
    ], 
];
