---
title: Configuration
description: Complete reference for all configuration options
section: configuration
order: 1
---

# Configuration

After running `php artisan geo:install`, the package configuration is published to `config/geo.php`. This file contains all settings for models, tables, geometry, and address behavior.

## Publishing Configuration

To publish or update the configuration file:

```bash
php artisan vendor:publish --tag=geo-config
```

## Configuration Reference

Here is the complete configuration structure:

```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Geographic Models
    |--------------------------------------------------------------------------
    |
    | Specify which Eloquent models to use for each geographic level.
    | You can extend the default models by pointing to custom classes.
    |
    */
    'models' => [
        'country' => \OiLab\Geo\Models\Country::class,
        'region' => \OiLab\Geo\Models\Region::class,
        'department' => \OiLab\Geo\Models\Department::class,
        'city' => \OiLab\Geo\Models\City::class,
        'borough' => \OiLab\Geo\Models\Borough::class,
        'address' => \OiLab\Geo\Models\Address::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Tables
    |--------------------------------------------------------------------------
    |
    | Customize the table names for each geographic level.
    | Update these to match existing tables or follow your naming conventions.
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
    | Geometry Support
    |--------------------------------------------------------------------------
    |
    | Enable spatial geometry features for location-based queries.
    | Requires PostGIS on PostgreSQL, or JSON storage on MySQL/SQLite.
    |
    */
    'enable_geometry' => true,

    /*
    |--------------------------------------------------------------------------
    | Spatial Reference ID
    |--------------------------------------------------------------------------
    |
    | SRID used for all geometry operations (default: 4326 = WGS84).
    | This is the standard coordinate system for GPS and web mapping.
    |
    */
    'srid' => 4326,

    /*
    |--------------------------------------------------------------------------
    | GeoJSON Import Path
    |--------------------------------------------------------------------------
    |
    | Directory where GeoJSON files are stored for the geo:seed command.
    | Expected files: countries.geojson, regions.geojson, departments.geojson,
    | cities.geojson, boroughs.geojson
    |
    */
    'geojson_path' => resource_path('geojson'),

    /*
    |--------------------------------------------------------------------------
    | Address Model Configuration
    |--------------------------------------------------------------------------
    |
    | Configure which geographic relationships the Address model includes.
    | This allows flexible address structures from simple to complex.
    |
    */
    'address' => [
        'include_city' => true,
        'include_country' => false,
        'include_department' => false,
        'include_region' => false,
    ],
];
```

## Configuration Options

### Models (`models`)

Override any of the six geographic models with your own custom classes:

```php
'models' => [
    'country' => App\Models\Geo\Country::class,
    'region' => App\Models\Geo\Region::class,
    // ... other models
],
```

See [Custom Models](/advanced/custom-models) for how to extend the default models.

### Tables (`tables`)

Customize the database table names:

```php
'tables' => [
    'countries' => 'geo_countries',
    'regions' => 'geo_regions',
    'departments' => 'geo_departments',
    'cities' => 'geo_cities',
    'boroughs' => 'geo_boroughs',
    'addresses' => 'geo_addresses',
],
```

### Geometry Support (`enable_geometry`)

Toggle spatial features on or off. When enabled:
- Cities and Addresses get a `location` point geometry column
- Regions and Departments get a `boundary` polygon geometry column

```php
'enable_geometry' => true, // or false
```

### Spatial Reference ID (`srid`)

The SRID (Spatial Reference ID) used for all geometry operations:

```php
'srid' => 4326, // WGS84 (GPS coordinates)
```

Standard values:
- `4326` — WGS84 (longitude, latitude — worldwide, decimal degrees)
- `3857` — Web Mercator (used by web maps)

### GeoJSON Path (`geojson_path`)

Directory for GeoJSON import files used by `php artisan geo:seed`:

```php
'geojson_path' => resource_path('geojson'),
```

Expected structure:
```
resources/geojson/
├── countries.geojson
├── regions.geojson
├── departments.geojson
├── cities.geojson
└── boroughs.geojson
```

### Address Configuration (`address`)

Control which geographic relationships the Address model includes:

```php
'address' => [
    'include_city' => true,      // city_id foreign key
    'include_country' => false,  // country_id foreign key
    'include_department' => false, // department_id foreign key
    'include_region' => false,   // region_id foreign key
],
```

- **`include_city`** — Adds `city_id` FK to Address. When false, use `city` string column instead
- **`include_country`** — Adds `country_id` FK to Address
- **`include_department`** — Adds `department_id` FK to Address
- **`include_region`** — Adds `region_id` FK to Address

## Environment-Specific Configuration

Use environment variables to customize configuration per environment:

```php
'enable_geometry' => env('GEO_ENABLE_GEOMETRY', true),
'geojson_path' => env('GEO_GEOJSON_PATH', resource_path('geojson')),
```

In your `.env` file:

```
GEO_ENABLE_GEOMETRY=true
GEO_GEOJSON_PATH=/var/data/geojson
```
