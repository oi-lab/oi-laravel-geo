# Changelog

All notable changes to `oi-laravel-geo` will be documented in this file.

## [1.0.2] - 2025-11-01

### Added
- **JSON storage support for MySQL and SQLite** - Geometry columns now use JSON format for databases without native spatial types
- New custom Eloquent casts: `PointCast` and `PolygonCast` for automatic data conversion
- Database-aware migration generation that adapts column types based on selected database driver
- `UPGRADE_GUIDE.md` documentation for migration from v1.x

### Changed
- **BREAKING**: `MigrationGenerator` now generates different column types based on database:
  - PostgreSQL: Native `point()` and `polygon()` types (with PostGIS)
  - MySQL/SQLite: `json()` columns for geometry storage
- `HasPoint` trait now uses `PointCast` and removes manual mutators/accessors
- `HasPolygon` trait now uses `PolygonCast` and simplifies coordinate handling
- `MySQLGeoDriver` adapted to work with JSON-stored geometries using Haversine formula
- `SQLiteGeoDriver` adapted to use `json_extract()` for JSON-stored geometries

### Improved
- Geometry data handling is now completely transparent across all supported databases
- Better performance for distance calculations on MySQL/SQLite using native JSON functions
- Consistent API regardless of underlying database storage format
- Automatic cast application through trait initialization

### Fixed
- **Critical**: POINT and POLYGON column types no longer fail on SQLite and MySQL
- Geometry columns now properly support SQLite for testing environments
- JSON-based geometry storage enables spatial queries on databases without PostGIS

### Notes
- For advanced spatial operations (accurate polygon containment, intersection, area calculation), PostgreSQL with PostGIS is recommended
- MySQL and SQLite implementations use simplified algorithms for polygon operations
- Migration from v1.x requires regenerating migrations with the new `geo:install` command

## [1.0.1] - 2025-11-01

### Added
- Dynamic migration timestamp generation with 1-second intervals to ensure proper ordering
- Automatic dependency resolution when generating partial model sets
- Smart migration publishing that respects foreign key constraints
- Support for selective model generation based on user configuration

### Changed
- Migration publishing now generates timestamps based on current date instead of using fixed dates
- MigrationGenerator now automatically includes required parent tables based on dependencies
- Address migration dependencies are now dynamically calculated based on configuration

### Improved
- Migration ordering is guaranteed through timestamp-based sequencing
- Better handling of foreign key relationships during migration publication
- Enhanced configuration support for `models_to_generate` in installation process

## [1.0.0] - 2025-10-30

### Added
- First stable release
