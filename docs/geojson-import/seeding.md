---
title: Seeding Geographic Data
description: Import geographic data from GeoJSON files with the geo:seed command
section: geojson-import
order: 1
---

# Seeding Geographic Data

The `geo:seed` command imports geographic records from GeoJSON files into your database. It handles the full hierarchy in the correct order.

## Command signature

```bash
php artisan geo:seed {type?} {--file=}
```

- `type` — one of `countries`, `regions`, `departments`, `cities`, `boroughs`, `all`
- `--file` — path to a custom GeoJSON file (overrides the default location)

## Examples

```bash
# Import all levels in order
php artisan geo:seed all

# Import specific level
php artisan geo:seed countries
php artisan geo:seed regions
php artisan geo:seed departments
php artisan geo:seed cities
php artisan geo:seed boroughs

# Import from a custom file
php artisan geo:seed countries --file=/path/to/countries.geojson
```

## File location

By default, GeoJSON files are read from the path configured in `config/oi-laravel-geo.php`:

```php
'geojson_path' => resource_path('geojson'),
```

Place your files at `resources/geojson/{type}.geojson`.

## GeoJSON file formats

### countries.geojson

```json
{
    "type": "FeatureCollection",
    "features": [
        {
            "type": "Feature",
            "properties": {
                "code": "FR",
                "name": "France",
                "population": 67000000,
                "surface": 551695
            },
            "geometry": {
                "type": "MultiPolygon",
                "coordinates": [[[[...]]]]
            }
        }
    ]
}
```

Required: `code`, `name`. Optional: `population`, `surface`, `geometry` (when geometry is enabled).

### regions.geojson

```json
{
    "type": "FeatureCollection",
    "features": [
        {
            "type": "Feature",
            "properties": {
                "country_code": "FR",
                "code": "IDF",
                "name": "Île-de-France",
                "population": 12174880,
                "surface": 12012
            },
            "geometry": { "type": "Polygon", "coordinates": [[[...]]] }
        }
    ]
}
```

Required: `country_code` (links to a Country), `code`, `name`.

### departments.geojson

```json
{
    "type": "FeatureCollection",
    "features": [
        {
            "type": "Feature",
            "properties": {
                "region_code": "IDF",
                "code": "75",
                "name": "Paris",
                "population": 2165423,
                "surface": 105
            },
            "geometry": { "type": "Polygon", "coordinates": [[[...]]] }
        }
    ]
}
```

Required: `region_code` (links to a Region), `code`, `name`.

### cities.geojson

```json
{
    "type": "FeatureCollection",
    "features": [
        {
            "type": "Feature",
            "properties": {
                "department_code": "75",
                "identifier": "75056",
                "code": "75056",
                "name": "Paris",
                "population": 2165423,
                "surface": 105
            },
            "geometry": { "type": "Point", "coordinates": [2.3522, 48.8566] }
        }
    ]
}
```

Required: `department_code`, `identifier` (globally unique), `code`, `name`.

### boroughs.geojson

```json
{
    "type": "FeatureCollection",
    "features": [
        {
            "type": "Feature",
            "properties": {
                "city_identifier": "75056",
                "code": "01",
                "name": "1er Arrondissement",
                "population": 16266,
                "surface": 183
            },
            "geometry": { "type": "Polygon", "coordinates": [[[...]]] }
        }
    ]
}
```

Required: `city_identifier` (links to a City's `identifier` field), `code`, `name`.

## Import order

When running `geo:seed all`, data is imported in the correct order to satisfy foreign key constraints:

```
1. Countries
2. Regions (requires countries)
3. Departments (requires regions)
4. Cities (requires departments)
5. Boroughs (requires cities)
```
