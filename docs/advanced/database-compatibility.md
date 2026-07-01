---
title: Database Compatibility
description: Spatial support across PostgreSQL, MySQL, and SQLite
section: advanced
order: 2
---

# Database Compatibility

## Compatibility matrix

| Feature | PostgreSQL + PostGIS | MySQL 5.7+ | SQLite |
|---------|---------------------|------------|--------|
| Point storage | Native POINT | JSON | JSON |
| Polygon storage | Native POLYGON | JSON | JSON |
| Radius search (`nearby`) | Native ST_DWithin | Haversine | Haversine |
| Bounding box (`withinBounds`) | Native | Computed | Computed |
| Point-in-polygon (`withinPolygon`) | Native ST_Contains | Simplified | Simplified |
| Polygon containment (`containsPoint`) | Native ST_Contains | Simplified | Simplified |
| Polygon intersection | Native ST_Intersects | Simplified | Simplified |
| Area calculation | Native ST_Area | ✗ | ✗ |
| Recommended for | Production | General use | Development |

## PostgreSQL + PostGIS

Full spatial support with native geometry operations. Install PostGIS before running migrations:

```bash
# Ubuntu / Debian
sudo apt install postgis

# macOS via Homebrew
brew install postgis

# Then enable in your database
psql -d your_database -c "CREATE EXTENSION IF NOT EXISTS postgis;"
```

Database URL example:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=your_database
DB_USERNAME=your_user
DB_PASSWORD=your_password
```

## MySQL

MySQL uses JSON columns to store geometry data. Haversine formula handles distance and radius queries accurately. Polygon containment and intersection use simplified bounding-box approximations — accurate enough for most use cases, but not pixel-perfect for complex boundary shapes.

No additional setup required beyond a standard MySQL installation (5.7+).

## SQLite

SQLite follows the same JSON-based approach as MySQL. It's suitable for development and testing but not recommended for production geographic workloads.

## Geometry storage formats

**PostgreSQL** stores geometry in WKT (Well-Known Text):
```
POINT(2.3522 48.8566)
POLYGON((2.22 48.82, 2.47 48.82, 2.47 48.92, 2.22 48.92, 2.22 48.82))
```

**MySQL / SQLite** stores geometry in JSON:
```json
{"longitude": 2.3522, "latitude": 48.8566}
[[2.22, 48.82], [2.47, 48.82], [2.47, 48.92], [2.22, 48.92]]
```

The `PointCast` and `PolygonCast` classes handle conversion transparently. Your PHP code uses the same API regardless of database:

```php
$city->location = [2.3522, 48.8566]; // Works on all databases
echo $city->latitude;                 // Always returns float
```
