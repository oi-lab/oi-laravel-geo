---
title: HasPoint Trait
description: Location point geometry for cities and addresses
section: spatial
order: 2
---

# HasPoint Trait

`OiLab\OiLaravelGeo\Traits\HasPoint` adds a `location` POINT column and proximity query scopes to a model.

## Adding the trait

```php
namespace App\Models;

use OiLab\OiLaravelGeo\Models\City as BaseCity;
use OiLab\OiLaravelGeo\Traits\HasPoint;

class City extends BaseCity
{
    use HasPoint;

    protected $fillable = [
        'department_id', 'identifier', 'code', 'name',
        'population', 'surface',
        'location', // add the geometry column
    ];
}
```

The installer generates this for you when you select a model during `geo:install`.

## Storing a location

Pass an array as `[longitude, latitude]` or with named keys:

```php
$city = City::find(1);

// Positional (longitude first — GeoJSON convention)
$city->location = [2.3522, 48.8566]; // Paris

// Named keys
$city->location = ['longitude' => 2.3522, 'latitude' => 48.8566];

$city->save();
```

## Reading coordinates

```php
$city = City::find(1);

$lat = $city->latitude;   // 48.8566
$lng = $city->longitude;  // 2.3522
```

## Query scopes

### nearby — radius search

Find all cities within a given radius of a coordinate:

```php
// Cities within 50 km of Paris
$cities = City::nearby(48.8566, 2.3522, 50)->get();

// With additional filters
$cities = City::nearby(48.8566, 2.3522, 100)
    ->where('population', '>', 10000)
    ->orderBy('name')
    ->get();
```

**Signature:** `nearby(float $lat, float $lng, float $radiusKm): Builder`

### withinBounds — bounding box

Find cities inside a rectangular bounding box:

```php
$cities = City::withinBounds(
    minLat: 48.5,
    minLng: 2.0,
    maxLat: 49.0,
    maxLng: 3.0
)->get();
```

**Signature:** `withinBounds(float $minLat, float $minLng, float $maxLat, float $maxLng): Builder`

### withinPolygon — arbitrary polygon

Find cities inside a custom polygon (array of `[longitude, latitude]` points):

```php
$polygon = [
    [-5.0, 42.0],
    [ 8.0, 42.0],
    [ 8.0, 51.0],
    [-5.0, 51.0],
];

$cities = City::withinPolygon($polygon)->get();
```

**Signature:** `withinPolygon(array $coordinates): Builder`

### withinCircle — alias for nearby

```php
$cities = City::withinCircle(48.8566, 2.3522, 50)->get();
```

## Instance method

### distanceTo

Calculate the straight-line distance in kilometres from this city to a coordinate:

```php
$paris = City::where('name', 'Paris')->first();
$distance = $paris->distanceTo(45.7640, 4.8357); // Distance to Lyon

echo round($distance, 1) . ' km'; // approx 391.6 km
```

**Signature:** `distanceTo(float $lat, float $lng): float`
