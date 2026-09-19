<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Custom Models
    |--------------------------------------------------------------------------
    |
    | Override default models with your own custom models.
    | Set to null to use the default package models.
    |
    */
    'models' => [
        'country' => null,
        'region' => null,
        'department' => null,
        'city' => null,
        'borough' => null,
        'address' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Table Names
    |--------------------------------------------------------------------------
    |
    | Customize the table names used by the package.
    |
    */
    'tables' => [
        'countries' => 'countries',
        'regions' => 'regions',
        'departments' => 'departments',
        'cities' => 'cities',
        'boroughs' => 'boroughs',
        'addresses' => 'addresses',
    ],

    /*
    |--------------------------------------------------------------------------
    | GeoJSON Resources
    |--------------------------------------------------------------------------
    |
    | Path to GeoJSON files for seeding geographic data.
    |
    */
    'geojson_path' => resource_path('geojson'),

    /*
    |--------------------------------------------------------------------------
    | Geometry Support
    |--------------------------------------------------------------------------
    |
    | Enable or disable geometry (Point/Polygon) support.
    | Requires spatial database support (MySQL 5.7+, PostgreSQL with PostGIS).
    |
    */
    'enable_geometry' => false,

    /*
    |--------------------------------------------------------------------------
    | Default SRID
    |--------------------------------------------------------------------------
    |
    | Spatial Reference System Identifier for geographic coordinates.
    | 4326 is the standard for WGS84 (GPS coordinates).
    |
    */
    'srid' => 4326,

    /*
    |--------------------------------------------------------------------------
    | Address Optional Fields
    |--------------------------------------------------------------------------
    |
    | Enable optional city_id, department_id, region_id and country_id fields on addresses.
    | Set to true to add these fields to the addresses table migration.
    |
    */
    'address_include_city' => false,
    'address_include_department' => false,
    'address_include_region' => false,
    'address_include_country' => false,

    /*
    |--------------------------------------------------------------------------
    | Polymorphic Addresses
    |--------------------------------------------------------------------------
    |
    | Attach addresses to any model (user, team, company site...) through an
    | `addressable_type` / `addressable_id` morph plus an `is_default` flag.
    | Enabling this adds those columns to the addresses table migration and
    | makes the `OiLab\OiLaravelGeo\Concerns\HasAddresses` trait usable.
    |
    */
    'address_morphable' => false,

    /*
    |--------------------------------------------------------------------------
    | Address Primary Key Type
    |--------------------------------------------------------------------------
    |
    | Type of the addresses primary key: 'id' for an auto-incrementing bigint
    | (default) or 'ulid' for a lexicographically sortable 26 character ULID.
    |
    | Supported: "id", "ulid"
    |
    */
    'address_key_type' => 'id',

    /*
    |--------------------------------------------------------------------------
    | Address Geocoding Columns
    |--------------------------------------------------------------------------
    |
    | Add decimal geocoding columns to addresses (latitude, longitude,
    | geocoded_label, geocoding_score, ban_id, geocoded_at). These are
    | independent from `enable_geometry`: the geometry column stores a Point,
    | these store human-readable decimals plus geocoding metadata.
    |
    | The package never calls a geocoding API — it only carries the columns.
    |
    */
    'address_geocoding' => false,
];
