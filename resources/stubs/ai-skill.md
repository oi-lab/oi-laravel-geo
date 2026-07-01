# OI Laravel Geo — AI Context

Hierarchical geographic data management for Laravel: Country → Region →
Department → City → Borough models, an Address model with flexible
configuration, GeoJSON bulk import, and optional Point/Polygon spatial support
(PostgreSQL native, JSON storage on MySQL/SQLite).

## Core Concepts

- **Country** (`countries`) — top of the hierarchy; `code` (unique), `name`, `population`, `surface`.
- **Region** (`regions`) — belongs to Country; unique per `country_id`+`code`.
- **Department** (`departments`) — belongs to Region.
- **City** (`cities`) — belongs to Department; has a unique `identifier`.
- **Borough** (`boroughs`) — belongs to City.
- **Address** (`addresses`) — `street_1..3`, `postal_code`, and either a plain
  `city` string (default) or FK relations to City/Country/Department/Region,
  toggled by the `address_include_*` config flags.
- All table names are configurable via `oi-laravel-geo.tables.*`; all models
  are swappable via `oi-laravel-geo.models.*` and resolved through the
  `OiLaravelGeo` facade (`OiLaravelGeo::getCityModel()` etc.) — never hardcode
  model classes.
- Every model exposes `toData()` returning a `spatie/laravel-data` DTO
  (`CountryData`, `RegionData`, `DepartmentData`, `CityData`, `BoroughData`,
  `AddressData` in `OiLab\OiLaravelGeo\Data`).

## Public API

```php
use OiLab\OiLaravelGeo\Facades\OiLaravelGeo;
use OiLab\OiLaravelGeo\Models\City;

$cityModel = OiLaravelGeo::getCityModel();          // configurable class string
$city = City::with('department.region.country')->firstWhere('code', '34172');
$data = $city->toData();                             // CityData DTO

$address->full_address;                              // computed accessor
$address->city_name;                                 // string or relation name
```

Spatial (optional, `enable_geometry`):

```php
use OiLab\OiLaravelGeo\Traits\HasPoint;   // + HasPolygon
// scopes: withinRadius(), nearest(), withinPolygon()… backed by
// MySQL/PostgreSQL/SQLite drivers (GeoQueryBuilder + PointCast/PolygonCast)
```

## Commands

```bash
php artisan geo:install               # interactive: config, migrations, stubs, geojson
php artisan geo:seed [type] [--file=] # import GeoJSON data (countries, regions, …)
php artisan vendor:publish --tag=oi-laravel-geo-config
```

## Configuration

- `models.*` — model class overrides (null = package default).
- `tables.*` — table name overrides.
- `geojson_path` — GeoJSON source dir (default `resource_path('geojson')`).
- `enable_geometry`, `srid` — spatial support.
- `address_include_city|department|region|country` — Address FK toggles.

## Host Integration Checklist

- Run the interactive installer (generates database-aware migrations) then
  `php artisan migrate`.
- Publish GeoJSON resources and seed with `geo:seed`.
- Override models by extending the package models and pointing
  `oi-laravel-geo.models.*` at your subclasses.

## Updating the AI Skill

After updating this package, re-sync the skill files:

```bash
composer sync-ai-skills
```
