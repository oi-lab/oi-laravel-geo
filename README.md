<img src="./assets/github-preview.png" alt="OI Laravel Geo" width="100%" />

# OI Laravel Geo

[![Latest Version on Packagist](https://img.shields.io/packagist/v/oi-lab/oi-laravel-geo.svg)](https://packagist.org/packages/oi-lab/oi-laravel-geo)
[![Total Downloads](https://img.shields.io/packagist/dt/oi-lab/oi-laravel-geo.svg)](https://packagist.org/packages/oi-lab/oi-laravel-geo)
[![Tests](https://img.shields.io/github/actions/workflow/status/oi-lab/oi-laravel-geo/tests.yml?label=tests)](https://github.com/oi-lab/oi-laravel-geo/actions)
[![License](https://img.shields.io/github/license/oi-lab/oi-laravel-geo)](LICENSE)

A comprehensive Laravel package for managing hierarchical geographic data
(Countries, Regions, Departments, Cities, Boroughs) with Address management,
GeoJSON bulk import, typed `spatie/laravel-data` DTOs and optional spatial
support.

## Features

- Hierarchical models: **Borough → City → Department → Region → Country**
- **Address model** with flexible configuration (string city by default, optional City/Country/Department/Region relations)
- Opt-in **polymorphic addresses** (`HasAddresses` trait, one default per holder) working across holders with mixed key types
- Opt-in **ULID primary key** and **geocoding columns** (coordinates, score, BAN identifier) on addresses
- Typed DTOs: every model exposes `toData()` returning a `spatie/laravel-data` object
- Interactive installer generating database-aware migrations (`geo:install`)
- GeoJSON import service for bulk data seeding (`geo:seed`)
- Optional **Point** and **Polygon** geometry support — native types on PostgreSQL, JSON storage on MySQL/SQLite
- All models and table names configurable; models resolved through the `OiLaravelGeo` facade
- Full Pest test suite

## How It Works

The package ships base models under `OiLab\OiLaravelGeo\Models`. The
interactive installer generates migrations adapted to your database driver and
(optionally) host-app model subclasses from stubs. Every collaborator class is
resolved through the `OiLaravelGeo` facade
(`OiLaravelGeo::getCityModel()` etc.), so your application can substitute its
own subclasses via configuration without touching package internals.

## Requirements

- PHP 8.2+
- Laravel 11, 12, or 13
- spatie/laravel-data ^4.23

## Installation

```bash
composer require oi-lab/oi-laravel-geo
```

### Interactive Installation (Recommended)

```bash
php artisan geo:install
```

The installer asks which models you need, whether to enable geometry support,
and how addresses should relate to the hierarchy, then generates the matching
migrations (and optional model stubs). Finish with:

```bash
php artisan migrate
```

### Manual Installation

```bash
php artisan vendor:publish --tag=oi-laravel-geo-config
php artisan vendor:publish --tag=oi-laravel-geo-stubs
php artisan vendor:publish --tag=oi-laravel-geo-geojson
```

## Configuration

Key options of `config/oi-laravel-geo.php`:

```php
return [
    'models' => [            // override any model with your own subclass
        'city' => null,      // e.g. App\Models\City::class
        // country, region, department, borough, address…
    ],
    'tables' => [            // customize table names
        'cities' => 'cities',
        // …
    ],
    'geojson_path' => resource_path('geojson'),
    'enable_geometry' => false,
    'srid' => 4326,
    'address_include_city' => false,       // city_id FK instead of a string
    'address_include_department' => false,
    'address_include_region' => false,
    'address_include_country' => false,
    'address_morphable' => false,          // attach addresses to any model
    'address_key_type' => 'id',            // 'id' (bigint) or 'ulid'
    'address_geocoding' => false,          // latitude/longitude + BAN metadata
];
```

The last three are opt-in and default to the pre-1.2 behaviour: an existing
installation that upgrades without touching its config generates exactly the
same migration and behaves exactly the same.

## Usage

### Models & DTOs

```php
use OiLab\OiLaravelGeo\Models\City;

$city = City::with('department.region.country')->firstWhere('code', '34172');

$city->department->region->country->name; // "France"

$data = $city->toData();   // CityData DTO (spatie/laravel-data)
$data->toArray();          // ready for APIs / Inertia props
```

### Addresses

```php
use OiLab\OiLaravelGeo\Models\Address;

$address = Address::create([
    'street_1' => '5 Avenue Anatole France',
    'city' => 'Paris',           // or city_id when address_include_city is on
    'postal_code' => '75007',
]);

$address->full_address;          // "5 Avenue Anatole France, 75007, Paris"
$address->toData();              // AddressData DTO
```

See the [Address documentation](docs/models/address.md) for the relation-based
variants.

### Attached Addresses and Geocoding

Three independent opt-in capabilities, all off by default.

**Polymorphic addresses** (`address_morphable`) add `addressable_type`,
`addressable_id` and `is_default` to the table, and make the `HasAddresses`
trait usable on any holder:

```php
use OiLab\OiLaravelGeo\Concerns\HasAddresses;

class Team extends Model
{
    use HasAddresses;
}

$team->addAddress([
    'street_1' => '1 Place de la Comédie',
    'city' => 'Montpellier',
    'postal_code' => '34000',
], default: true);

$team->addresses;          // MorphMany
$team->defaultAddress();   // ?Address — the one flagged is_default
$address->addressable;     // MorphTo back to the holder
```

`addressable_id` is a plain `string(36)`, not `$table->morphs()`. Holders
legitimately have different key types — a `User` and a `Team` on an
auto-incrementing bigint, an `Entity` on a ULID — and neither `morphs()` nor
`ulidMorphs()` can be both at once. A 36-character string holds a bigint, a
ULID (26 chars) and a UUID (36 chars) alike, and Eloquent compares them
correctly in both directions. The `['addressable_type', 'addressable_id']`
index is declared by hand for the same reason.

Only one address per holder can be the default. MySQL has no partial unique
index, so the invariant is enforced on write by `AddressObserver`, which flips
the holder's other defaults to `false`.

**ULID primary key** (`address_key_type = 'ulid'`) switches the table's key
from `$table->id()` to `$table->ulid('id')->primary()`. The model implements
`getKeyType()`, `getIncrementing()` and the `creating` hook by hand rather than
using `HasUlids`, because that trait decides the key type statically while here
it comes from the configuration.

**Geocoding columns** (`address_geocoding`) add `latitude` and `longitude`
(`decimal(10,7)`), `geocoded_label`, `geocoding_score` (`decimal(4,3)`),
`ban_id` and `geocoded_at`:

```php
$address->isGeocoded();    // true once latitude and longitude are both set
$address->toData();        // AddressData carries all of the above
```

These columns **do not replace `enable_geometry`** and the two mechanisms are
independent. The geometry column stores a `Point` — native in PostgreSQL, JSON
on MySQL and SQLite — which is what spatial queries need. The geocoding columns
store readable decimals, for rounding a cache key, comparing, or displaying,
plus metadata a `Point` cannot carry (the provider score, the BAN identifier).
Enable either, both, or neither.

**The package never geocodes.** It carries the columns and the contract, not
the network call: filling them is the application's job.

### Importing GeoJSON Data

```bash
php artisan geo:seed             # import everything found in geojson_path
php artisan geo:seed cities      # import a specific type
php artisan geo:seed cities --file=custom/cities.geojson
```

See [GeoJSON import](docs/geojson-import/seeding.md) for file formats.

### Geometry Support (Optional)

Enable `enable_geometry` and add the traits to your models:

```php
use OiLab\OiLaravelGeo\Traits\HasPoint;
use OiLab\OiLaravelGeo\Traits\HasPolygon;

City::withinRadius('location', 48.8566, 2.3522, 50)->get();
Country::containsPoint(48.8566, 2.3522)->get();
```

PostgreSQL uses native geometry types; MySQL and SQLite fall back to JSON
storage with driver-appropriate queries. See
[Spatial documentation](docs/spatial/_index.md) and
[database compatibility](docs/advanced/database-compatibility.md).

### Custom Models

Extend any package model and register it in the config; every relation across
the package will resolve your subclass. See
[custom models](docs/advanced/custom-models.md).

## AI Assistant Skills

This package ships an AI assistant skill teaching Claude (and other agents)
its models, commands and conventions:

```bash
php artisan vendor:publish --tag=oi-laravel-geo-skill
```

## Testing

```bash
composer test
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

When contributing:
1. Write tests for new features
2. Ensure all tests pass: `vendor/bin/pest`
3. Follow existing code style
4. Update documentation as needed

## License

The MIT License (MIT). Please see the [License File](LICENSE) for more information.

## Credits

**[Olivier Lacombe](https://www.olacombe.com)** - Creator and maintainer

Olivier is a Product & Technology Director based in Montpellier, France, with over 20 years of experience innovating in UX/UI and emerging technologies. He specializes in guiding enterprises toward cutting-edge digital solutions, combining user-centered design with continuous optimization and artificial intelligence integration.

**Projects & Resources:**
- [OI Dev Docs](https://dev.olacombe.com) - Documentation for all Open Source OI Lab packages
- [OnAI](https://onai.olacombe.com) - Training courses and masterclasses on generative AI for businesses
- [Promptr](https://promptr.olacombe.com) - Prompt engineering Management Platform

## Support

For support, please open an issue on the [GitHub repository](https://github.com/oi-lab/oi-laravel-geo/issues).
