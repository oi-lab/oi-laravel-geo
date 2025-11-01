# Changelog

All notable changes to `oi-laravel-geo` will be documented in this file.

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
