# OiLaravelGeo - Laravel Geographic Data Package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/oi-lab/oi-laravel-geo.svg?style=flat-square)](https://packagist.org/packages/oi-lab/oi-laravel-geo)
[![Total Downloads](https://img.shields.io/packagist/dt/oi-lab/oi-laravel-geo.svg?style=flat-square)](https://packagist.org/packages/oi-lab/oi-laravel-geo)

A comprehensive Laravel package for managing hierarchical geographic data (Countries, Regions, Departments, Cities, Boroughs) with Address management and optional spatial support.

## Features

- Hierarchical models: **Borough → City → Department → Region → Country**
- **Address model** with flexible configuration (city, country, optional department/region)
- Database migrations with customizable table names
- GeoJSON import service for bulk data seeding
- Optional **Point** and **Polygon** geometry support
- Model customization through stubs
- Artisan commands for easy installation and data management
- Full test coverage with Pest
- Laravel 11+ and PHP 8.2+ support

## Installation

Install the package via Composer:

```bash
composer require oi-lab/oi-laravel-geo
```

Run the installation command:

```bash
php artisan oi-laravel-geo:install
```

This will:
- Publish the configuration file
- Publish migrations
- Publish model stubs
- Publish GeoJSON resource directory

Run migrations:

```bash
php artisan migrate
```

## Configuration

The configuration file is published at `config/oi-laravel-geo.php`:

```php
return [
    // Override default models with your custom models
    'models' => [
        'country' => null,
        'region' => null,
        'department' => null,
        'city' => null,
        'borough' => null,
        'address' => null,
    ],

    // Customize table names
    'tables' => [
        'countries' => 'countries',
        'regions' => 'regions',
        'departments' => 'departments',
        'cities' => 'cities',
        'boroughs' => 'boroughs',
        'addresses' => 'addresses',
    ],

    // Enable geometry (Point/Polygon) support
    'enable_geometry' => false,

    // GeoJSON files path
    'geojson_path' => resource_path('geojson'),

    // Spatial Reference System Identifier (WGS84)
    'srid' => 4326,

    // Address optional fields
    'address_include_city' => false,        // Use city_id foreign key instead of city string
    'address_include_country' => false,     // Add country_id foreign key
    'address_include_department' => false,  // Add department_id foreign key
    'address_include_region' => false,      // Add region_id foreign key
];
```

## Usage

### Basic Model Usage

```php
use OiLab\OiLaravelGeo\Models\Country;
use OiLab\OiLaravelGeo\Models\Region;
use OiLab\OiLaravelGeo\Models\Department;
use OiLab\OiLaravelGeo\Models\City;
use OiLab\OiLaravelGeo\Models\Borough;

// Create a country
$france = Country::create([
    'code' => 'FR',
    'name' => 'France',
    'population' => 67000000,
    'surface' => 551695,
]);

// Access relationships
$regions = $france->regions;
$departments = $region->departments;
$cities = $department->cities;
$boroughs = $city->boroughs;
```

### Custom Models

Publish stubs and create your custom models:

```bash
php artisan oi-laravel-geo:install --stubs
```

Create your custom model in `app/Models/Country.php`:

```php
namespace App\Models;

use OiLab\OiLaravelGeo\Models\Country as BaseCountry;

class Country extends BaseCountry
{
    // Add your custom methods and relationships
    public function customMethod()
    {
        // Your logic here
    }
}
```

Update your configuration in `config/oi-laravel-geo.php`:

```php
'models' => [
    'country' => \App\Models\Country::class,
],
```

### Importing GeoJSON Data

Place your GeoJSON files in `resources/geojson/`:
- `countries.geojson`
- `regions.geojson`
- `departments.geojson`
- `cities.geojson`
- `boroughs.geojson`

Required GeoJSON properties structure:

**Countries:**
```json
{
  "type": "FeatureCollection",
  "features": [
    {
      "type": "Feature",
      "properties": {
        "code": "FR",
        "name": "France",
        "population": 67000000,
        "surface": 551695
      },
      "geometry": {...}
    }
  ]
}
```

**Regions:**
```json
{
  "properties": {
    "country_code": "FR",
    "code": "IDF",
    "name": "Île-de-France",
    "population": 12000000,
    "surface": 12012
  }
}
```

**Departments:**
```json
{
  "properties": {
    "region_code": "IDF",
    "code": "75",
    "name": "Paris",
    "population": 2200000,
    "surface": 105
  }
}
```

**Cities:**
```json
{
  "properties": {
    "department_code": "75",
    "identifier": "75056",
    "code": "75056",
    "name": "Paris",
    "population": 2200000,
    "surface": 105
  }
}
```

**Boroughs:**
```json
{
  "properties": {
    "city_identifier": "75056",
    "code": "01",
    "name": "1er Arrondissement",
    "population": 16000,
    "surface": 183
  }
}
```

Import data using the Artisan command:

```bash
# Import all data
php artisan oi-laravel-geo:seed all

# Import specific type
php artisan oi-laravel-geo:seed countries
php artisan oi-laravel-geo:seed regions
php artisan oi-laravel-geo:seed departments
php artisan oi-laravel-geo:seed cities
php artisan oi-laravel-geo:seed boroughs

# Import from custom file
php artisan oi-laravel-geo:seed countries --file=/path/to/custom.geojson
```

### Geometry Support (Optional)

Enable geometry support in `config/oi-laravel-geo.php`:

```php
'enable_geometry' => true,
```

Add geometry columns to your custom migrations:

```php
Schema::table('cities', function (Blueprint $table) {
    $table->point('location')->nullable();
});

Schema::table('departments', function (Blueprint $table) {
    $table->polygon('boundary')->nullable();
});
```

Use geometry traits in your models:

```php
use OiLab\OiLaravelGeo\Models\City as BaseCity;
use OiLab\OiLaravelGeo\Traits\HasPoint;

class City extends BaseCity
{
    use HasPoint;

    protected $fillable = ['location', ...];
}
```

**Point Geometry Usage:**

```php
// Set location
$city->location = ['latitude' => 48.8566, 'longitude' => 2.3522];
$city->save();

// Get coordinates
$latitude = $city->latitude;
$longitude = $city->longitude;

// Find nearby cities (within 10km)
$nearbyCities = City::nearby(48.8566, 2.3522, 10)->get();

// Find cities within bounds
$cities = City::withinBounds($minLat, $minLng, $maxLat, $maxLng)->get();
```

**Polygon Geometry Usage:**

```php
use OiLab\OiLaravelGeo\Traits\HasPolygon;

class Department extends BaseDepartment
{
    use HasPolygon;

    protected $fillable = ['boundary', ...];
}

// Set boundary
$department->boundary = [
    [2.3, 48.8],
    [2.4, 48.9],
    [2.5, 48.8],
    [2.3, 48.8], // Closed polygon
];

// Get coordinates
$coordinates = $department->boundary_coordinates;

// Find departments containing a point
$departments = Department::containsPoint(48.8566, 2.3522)->get();

// Calculate area
$areaKm2 = $department->getAreaInSquareKilometers();
```

## Publishing Assets

Publish individual components:

```bash
# Configuration only
php artisan oi-laravel-geo:install --config

# Migrations only
php artisan oi-laravel-geo:install --migrations

# Stubs only
php artisan oi-laravel-geo:install --stubs

# GeoJSON directory only
php artisan oi-laravel-geo:install --geojson

# Force overwrite existing files
php artisan oi-laravel-geo:install --force
```

## Database Schema

### Countries
- `id` (primary key)
- `code` (string, unique)
- `name` (string)
- `population` (integer, nullable)
- `surface` (integer, nullable)
- `timestamps`

### Regions
- `id` (primary key)
- `country_id` (foreign key → countries)
- `code` (string)
- `name` (string)
- `population` (integer, nullable)
- `surface` (integer, nullable)
- `timestamps`
- **Unique:** `country_id + code`

### Departments
- `id` (primary key)
- `region_id` (foreign key → regions)
- `code` (string)
- `name` (string)
- `population` (integer, nullable)
- `surface` (integer, nullable)
- `timestamps`
- **Unique:** `region_id + code`

### Cities
- `id` (primary key)
- `department_id` (foreign key → departments)
- `identifier` (string, unique)
- `code` (string)
- `name` (string)
- `population` (integer, nullable)
- `surface` (integer, nullable)
- `timestamps`
- **Unique:** `department_id + code`, `identifier`

### Boroughs
- `id` (primary key)
- `city_id` (foreign key → cities)
- `code` (string)
- `name` (string)
- `population` (integer, nullable)
- `surface` (integer, nullable)
- `timestamps`
- **Unique:** `city_id + code`

### Addresses
- `id` (primary key)
- `name` (string) - Building or place name
- `street_1` (string) - Primary street address
- `street_2` (string, nullable) - Additional address line
- `street_3` (string, nullable) - Additional address line
- `city` (string) - City name (default) **OR** `city_id` (foreign key → cities, if `address_include_city` is true)
- `postal_code` (string)
- `country_id` (foreign key → countries, optional) - Only if `address_include_country` is true
- `department_id` (foreign key → departments, optional) - Only if `address_include_department` is true
- `region_id` (foreign key → regions, optional) - Only if `address_include_region` is true
- `timestamps`

**Configuration Options:**

By default, addresses use a simple string `city` field and no country relationship. You can enable foreign key relationships:

```php
// Default: city as string, no country
'address_include_city' => false,
'address_include_country' => false,

// Enable foreign keys to geographic entities
'address_include_city' => true,        // Use city_id instead of city string
'address_include_country' => true,     // Add country_id
'address_include_department' => true,  // Add department_id
'address_include_region' => true,      // Add region_id
```

## Address Usage

### Basic Address (Default Configuration)

By default, addresses use a simple string for the city:

```php
use OiLab\OiLaravelGeo\Models\Address;

$address = Address::create([
    'name' => 'Eiffel Tower',
    'street_1' => '5 Avenue Anatole France',
    'city' => 'Paris',
    'postal_code' => '75007',
]);

// Get full formatted address
echo $address->full_address;
// Output: "Eiffel Tower, 5 Avenue Anatole France, 75007, Paris"

// Get city name (works with both city string and city_id)
echo $address->city_name; // "Paris"
```

### Address with Foreign Keys (Optional)

Enable geographic relationships in config:

```php
'address_include_city' => true,
'address_include_country' => true,
```

Then create addresses with relationships:

```php
$address = Address::create([
    'name' => 'Eiffel Tower',
    'street_1' => '5 Avenue Anatole France',
    'city_id' => $city->id,
    'postal_code' => '75007',
    'country_id' => $country->id,
]);

// Access related models
$cityModel = $address->city;
$countryModel = $address->country;

echo $address->full_address;
// Output: "Eiffel Tower, 5 Avenue Anatole France, 75007, Paris, France"
```

### Address with Multiple Lines

```php
$address = Address::create([
    'name' => 'Company Name',
    'street_1' => '123 Main Street',
    'street_2' => 'Building B',
    'street_3' => 'Floor 5',
    'city' => 'New York',
    'postal_code' => '10001',
]);
```

### Address Relationships (When Enabled)

```php
// If address_include_city is true
if (Address::hasCityRelation()) {
    $cityModel = $address->city;
    $cityAddresses = $city->addresses;
}

// If address_include_country is true
if (Address::hasCountryRelation()) {
    $countryModel = $address->country;
    $countryAddresses = $country->addresses;
}

// If address_include_department is true
if (Address::hasDepartmentRelation()) {
    $departmentModel = $address->department;
}

// If address_include_region is true
if (Address::hasRegionRelation()) {
    $regionModel = $address->region;
}
```

## Testing

Run tests with Pest:

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Security

If you discover any security-related issues, please email contact@oi-lab.com instead of using the issue tracker.

## Credits

- [OI Lab](https://github.com/oi-lab)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
