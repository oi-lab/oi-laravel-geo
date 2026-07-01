---
title: HasPolygon Trait
description: Boundary polygon geometry for regions, departments, and countries
section: spatial
order: 3
---

# HasPolygon Trait

`OiLab\OiLaravelGeo\Traits\HasPolygon` adds a `boundary` POLYGON column and spatial query scopes to administrative area models.

## Adding the trait

```php
namespace App\Models;

use OiLab\OiLaravelGeo\Models\Department as BaseDepartment;
use OiLab\OiLaravelGeo\Traits\HasPolygon;

class Department extends BaseDepartment
{
    use HasPolygon;

    protected $fillable = [
        'region_id', 'code', 'name',
        'population', 'surface',
        'boundary', // add the geometry column
    ];
}
```

## Storing a boundary

Pass an array of `[longitude, latitude]` coordinate pairs. The polygon is closed automatically — no need to repeat the first point at the end:

```php
$dept = Department::find(1);

$dept->boundary = [
    [2.22, 48.82],
    [2.47, 48.82],
    [2.47, 48.92],
    [2.22, 48.92],
];

$dept->save();
```

## Reading coordinates

```php
$dept = Department::find(1);

$coordinates = $dept->boundary_coordinates; // array of [lng, lat] pairs
```

## Query scopes

### containsPoint

Find polygons that contain a given GPS coordinate:

```php
// Which department contains the Eiffel Tower?
$departments = Department::containsPoint(48.8584, 2.2945)->get();
```

**Signature:** `containsPoint(float $lat, float $lng): Builder`

### intersectsPolygon

Find polygons overlapping with another polygon:

```php
$searchArea = [
    [0.0, 45.0],
    [10.0, 45.0],
    [10.0, 50.0],
    [0.0, 50.0],
];

$departments = Department::intersectsPolygon($searchArea)->get();
// alias: intersects($searchArea)
```

**Signature:** `intersectsPolygon(array $coordinates): Builder`

### intersectsBounds

Find polygons overlapping with a bounding box:

```php
$departments = Department::intersectsBounds(
    minLat: 48.5,
    minLng: 2.0,
    maxLat: 49.0,
    maxLng: 3.0
)->get();
// alias: intersectsRectangle(...)
```

**Signature:** `intersectsBounds(float $minLat, float $minLng, float $maxLat, float $maxLng): Builder`

### intersectsCircle

Find polygons overlapping with a circular area:

```php
// Departments within or touching a 100 km radius around Paris
$departments = Department::intersectsCircle(48.8566, 2.3522, 100)->get();
```

**Signature:** `intersectsCircle(float $lat, float $lng, float $radiusKm): Builder`

## Instance method

### getAreaInSquareKilometers

Calculate the polygon area in km². **Only available on PostgreSQL with PostGIS.** Returns `null` on MySQL and SQLite.

```php
$dept = Department::find(1);
$area = $dept->getAreaInSquareKilometers();

if ($area !== null) {
    echo round($area, 1) . ' km²';
}
```

**Signature:** `getAreaInSquareKilometers(): ?float`
