<?php

return [

    /*
    | The shop listing resolved from the Google Maps share link. The embed
    | uses that listing's place id so the pin matches the map, not a nearby search result.
    */
    'location' => [
        'name' => 'Trend & Attire T & A',
        'alternate_name' => 'Trend & Attire',
        'lines' => [
            'Suit F-21, Saima Paari Star',
            'Block H, near Five Star Chowrangi',
            'North Nazimabad, Karachi 74600',
            'Pakistan',
        ],
        'street_address' => 'H, Suit F-21, Saima Paari Star, Block, near Five Star Chowrangi, North Block H Nazimabad',
        'locality' => 'Karachi',
        'region' => 'Sindh',
        'postal_code' => '74600',
        'country' => 'Pakistan',
        'country_code' => 'PK',
        'phone_display' => '0331 3898742',
        'phone' => '+923313898742',
        'latitude' => 24.9414297,
        'longitude' => 67.0471507,
        'maps_url' => 'https://maps.app.goo.gl/2YCJHnG91GyaQY999',
        'embed_url' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3618.5!2d67.047156!3d24.941431!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3eb33fcd52fb56d9%3A0xd1dda7b545207e1a!2sTrend%20%26%20Attire!5e0!3m2!1sen!2spk!4v1710000000000',
        'hours' => 'Monday–Saturday, 3:30–9:00 pm. Sunday closed.',
    ],

    'payments' => [
        'stripe' => [
            'key' => env('STRIPE_KEY'),
            'secret' => env('STRIPE_SECRET'),
            'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        ],
        'paypal' => [
            'client_id' => env('PAYPAL_CLIENT_ID'),
            'secret' => env('PAYPAL_SECRET'),
            'webhook_id' => env('PAYPAL_WEBHOOK_ID'),
            'base_url' => env('PAYPAL_BASE_URL', 'https://api-m.sandbox.paypal.com'),
        ],
    ],

];
