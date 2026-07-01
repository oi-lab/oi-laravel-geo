---
title: Installation
description: Install via Composer and run the interactive wizard
section: getting-started
order: 2
---

# Installation

## Using Composer

Install the package via Composer:

```bash
composer require oi-lab/oi-laravel-geo
```

## Interactive Setup Wizard

After installing via Composer, run the setup wizard:

```bash
php artisan geo:install
```

This interactive command guides you through configuration choices:

### Database Type
Choose your database engine:
- `postgres` — PostgreSQL with optional PostGIS support
- `mysql` — MySQL 8.0+
- `sqlite` — SQLite for development

### Geometry Support
Select whether to enable optional spatial features:
- Enables `point` geometry for Cities and Addresses
- Enables `polygon` geometry for Regions and Departments
- Requires PostGIS on PostgreSQL, or JSON storage on MySQL/SQLite

### Model Selection
Choose which models to publish:
- `country`, `region`, `department`, `city`, `borough`, `address`
- Allows you to extend models with custom logic

### Address Configuration
Define how the Address model relates to geographic hierarchy:
- Whether to include `city_id` foreign key
- Whether to include `country_id` and `department_id` foreign keys
- Whether to include `region_id` foreign key

## Manual Installation with Flags

You can also run the installation with flags to bypass the wizard:

```bash
php artisan geo:install \
  --database=postgres \
  --geometry=true \
  --models=country,region,department,city,borough,address \
  --address-city \
  --address-country \
  --address-department \
  --address-region \
  --force
```

### Installation Flags

| Flag | Values | Description |
|------|--------|-------------|
| `--database` | `postgres`, `mysql`, `sqlite` | Database engine (default: prompt) |
| `--geometry` | `true`, `false` | Enable spatial features (default: prompt) |
| `--models` | Comma-separated list | Models to publish (default: all) |
| `--address-city` | — | Include city relationship on Address |
| `--address-country` | — | Include country relationship on Address |
| `--address-department` | — | Include department relationship on Address |
| `--address-region` | — | Include region relationship on Address |
| `--config` | — | Only publish configuration file |
| `--migrations` | — | Only publish database migrations |
| `--stubs` | — | Only publish model stubs |
| `--geojson` | — | Only publish GeoJSON example files |
| `--interactive` | — | Force interactive prompts |
| `--force` | — | Overwrite existing files |

## What Gets Published

The installation process publishes:

1. **Configuration** (`config/geo.php`) — All geographic model and table settings
2. **Migrations** (`database/migrations/`) — Database tables for all geographic levels
3. **Models** (optional, `app/Models/Geo/`) — Eloquent models for customization
4. **GeoJSON Examples** (`resources/geojson/`) — Sample data files for seeding

## Post-Installation Steps

After running `geo:install`:

1. Review `config/geo.php` for any custom table names or model paths
2. Run migrations:
   ```bash
   php artisan migrate
   ```
3. (Optional) Seed geographic data from GeoJSON files:
   ```bash
   php artisan geo:seed --countries
   ```

## Next Steps

- Read [Configuration](/configuration) to customize table names and model paths
- Learn how to [Seed Geographic Data](/geojson-import/seeding)
- Explore [Models](/models) to understand the relationships
