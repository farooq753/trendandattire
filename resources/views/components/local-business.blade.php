@php
    $location = config('store.location');
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'ClothingStore',
        'name' => $location['name'],
        'alternateName' => $location['alternate_name'],
        'url' => url('/'),
        'telephone' => $location['phone'],
        'hasMap' => $location['maps_url'],
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => $location['street_address'],
            'addressLocality' => $location['locality'],
            'addressRegion' => $location['region'],
            'postalCode' => $location['postal_code'],
            'addressCountry' => $location['country_code'],
        ],
        'geo' => [
            '@type' => 'GeoCoordinates',
            'latitude' => $location['latitude'],
            'longitude' => $location['longitude'],
        ],
        'openingHoursSpecification' => [
            [
                '@type' => 'OpeningHoursSpecification',
                'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
                'opens' => '15:30',
                'closes' => '21:00',
            ],
        ],
    ];
@endphp
<script type="application/ld+json">
@json($schema)
</script>
