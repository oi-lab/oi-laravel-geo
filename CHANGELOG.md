# Changelog

All notable changes to `oi-laravel-geo` will be documented in this file.

## [Unreleased]

### Added
- Initial release
- Country, Region, Department, City, Borough, and Address models with hierarchical relationships
- Database migrations for all geographic entities
- GeoJSON import service for bulk data loading (Country, Region, Department, City, Borough)
- Address model with optional department_id and region_id fields
- Point and Polygon geometry traits (optional)
- CLI commands for installation and data seeding
- Model stubs for easy customization
- Configurable table names and model overrides
- Comprehensive test suite with Pest

## [1.0.0] - 2025-01-31

### Added
- First stable release
