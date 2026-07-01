---
title: Introduction
description: Discover OI Laravel Geo and its geographic hierarchy
section: getting-started
order: 1
---

# OI Laravel Geo

OI Laravel Geo is a comprehensive Laravel package for managing hierarchical geographic data. It provides Eloquent models for a five-level geographic hierarchy, optional spatial features with point and polygon support, and seamless database compatibility across PostgreSQL, MySQL, and SQLite.

## Geographic Hierarchy

The package structures geographic data in a strict five-level hierarchy:

1. **Country** — Top-level geographic entities
2. **Region** — Subdivisions within countries
3. **Department** — Subdivisions within regions
4. **City** — Urban centers within departments
5. **Borough** — Neighborhoods or subdivisions within cities
6. **Address** — Individual street addresses (optional geographic relationships)

Each level maintains relationships with its parent and children, supporting both eager loading and efficient queries.

## Key Features

- **Hierarchical Models** — Built-in Eloquent models for all five geographic levels
- **Flexible Addresses** — Address model with optional foreign key relationships to cities or string-based city names
- **Spatial Support** — Optional point and polygon geometry for precise location queries
- **Database Agnostic** — PostgreSQL with PostGIS, MySQL with JSON-based geometry, or SQLite for development
- **GeoJSON Import** — Seed your database from standardized GeoJSON files
- **Cascade Relationships** — Automatic cascade deletes maintain data integrity
- **Type-Safe** — Full PHP 8.2+ type hints and modern Laravel conventions

## Database Support

| Database | Geometry | Performance | Typical Use |
|----------|----------|-------------|-----------|
| PostgreSQL + PostGIS | Native spatial indexes | Excellent | Production applications |
| MySQL | JSON with Haversine | Good | Production applications |
| SQLite | JSON with Haversine | Fair | Development/testing |

## Requirements

- **PHP** 8.2 or higher
- **Laravel** 11.0 or higher
- **Database** PostgreSQL, MySQL 8.0+, or SQLite 3.9+

## What's Next?

- Read [Installation](/getting-started/installation) to set up the package
- Explore [Models](/models) to understand the geographic hierarchy
- Learn about [Spatial Features](/spatial) for location-based queries
- Check [Configuration](/configuration) for all available options
