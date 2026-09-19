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
- Three **opt-in** Address capabilities, all off by default and fully
  backward compatible:
  - `address_morphable` — `addressable_type` / `addressable_id` / `is_default`
    columns, an `addressable()` morphTo, and the
    `OiLab\OiLaravelGeo\Concerns\HasAddresses` trait for holders.
  - `address_key_type` — `'id'` (bigint, default) or `'ulid'`.
  - `address_geocoding` — `latitude`, `longitude`, `geocoded_label`,
    `geocoding_score`, `ban_id`, `geocoded_at` columns.
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

Attached addresses (`address_morphable`):

```php
use OiLab\OiLaravelGeo\Concerns\HasAddresses;      // on the holder model

$team->addresses();                                  // MorphMany
$team->defaultAddress();                             // ?Address (is_default)
$team->addAddress([...], default: true);             // Address
$address->addressable;                               // MorphTo back to holder
```

Geocoding (`address_geocoding`):

```php
$address->isGeocoded();      // latitude && longitude both set
$address->latitude;          // decimal:7 cast
$address->geocoding_score;   // decimal:3 cast
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
- `address_morphable` (false) — polymorphic holder + `is_default`.
- `address_key_type` ('id') — `'id'` or `'ulid'` primary key on addresses.
- `address_geocoding` (false) — geocoding columns on addresses.

## Rules and Gotchas

- `addressable_id` is a deliberate `string(36)`, never `$table->morphs()` /
  `$table->ulidMorphs()`: holders may have different key types (bigint `User`,
  ULID `Entity`) in the same addresses table. Do not "simplify" it.
- The ULID primary key is implemented by hand (`getKeyType()`,
  `getIncrementing()`, a `creating` hook) because the key type comes from
  config. Do not swap in the `HasUlids` trait.
- One default address per holder is enforced by `AddressObserver` on write —
  MySQL has no partial unique index.
- The geocoding columns are **independent** from `enable_geometry`: the
  geometry column stores a `Point` for spatial queries, the geocoding columns
  store readable decimals plus score and BAN metadata.
- **The package never geocodes.** It carries the columns only; the network
  call belongs to the application.

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
