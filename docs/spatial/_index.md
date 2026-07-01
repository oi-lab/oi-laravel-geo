---
title: Spatial Overview
description: Point and polygon geometry features for geographic queries
section: spatial
order: 1
---

# Spatial Features

When `enable_geometry` is set to `true`, the package adds geometry columns to your geographic models and unlocks powerful spatial query capabilities.

## Two geometry types

The package provides two traits covering the two most common geometry types:

| Trait | Geometry | Models | Use case |
|-------|----------|--------|----------|
| `HasPoint` | POINT | City, Address | Store a single GPS coordinate |
| `HasPolygon` | POLYGON | Country, Region, Department, Borough | Store an administrative boundary |

## Enabling geometry

```php
// config/oi-laravel-geo.php
'enable_geometry' => true,
'srid' => 4326, // WGS84 — standard GPS coordinate system
```

During `php artisan geo:install`, the installer asks which models should receive geometry columns. Choose:
- **Cities** and **Addresses** → get a `location` POINT column
- **Countries**, **Regions**, **Departments**, **Boroughs** → get a `boundary` POLYGON column

## Database behaviour

The same PHP API works across all supported databases. The difference is internal:

| Database | Storage format | Spatial engine |
|----------|---------------|----------------|
| PostgreSQL + PostGIS | Native WKT geometry | Full PostGIS functions |
| MySQL 5.7+ | JSON column | Haversine formula |
| SQLite | JSON column | Haversine formula |

PostgreSQL with PostGIS is the recommended choice for production systems requiring polygon containment, intersection, or area calculations.

## Next steps

- [HasPoint Trait](has-point.md) — location queries for cities and addresses
- [HasPolygon Trait](has-polygon.md) — boundary queries for regions and departments
