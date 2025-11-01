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

### Interactive Installation (Recommended)

Run the interactive installation command:

```bash
php artisan geo:install
```

This will guide you through an interactive setup where you can:
1. **Select your database type** (MySQL, PostgreSQL, or SQLite)
2. **Enable geometry support** for spatial queries
3. **Choose which models need geometry** (point/polygon columns)
4. **Configure address relationships** (foreign keys vs simple strings)
5. **Select models to generate** in your app/Models directory

The installer will automatically:
- ✅ Generate custom migrations based on your choices
- ✅ Generate custom models with appropriate traits
- ✅ Update configuration file
- ✅ Publish GeoJSON resource directory

### Manual Installation

If you prefer manual setup, you can publish components individually:

```bash
# Publish configuration only
php artisan geo:install --config

# Publish migrations only
php artisan geo:install --migrations

# Publish model stubs only
php artisan geo:install --stubs

# Publish GeoJSON directory only
php artisan geo:install --geojson

# Force overwrite existing files
php artisan geo:install --force
```

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

#### Database Compatibility

The geometry features support **MySQL 5.7+**, **PostgreSQL with PostGIS**, and **SQLite** (with limited functionality). The package automatically detects your database driver and uses the appropriate spatial functions.

**Note:** For production use with SQLite, consider installing the SpatiaLite extension for full spatial support.

#### Point Geometry Usage

The `HasPoint` trait provides methods for working with POINT geometry columns:

```php
// Set location (accepts array with longitude/latitude or named keys)
$city->location = [2.3522, 48.8566]; // [longitude, latitude]
// OR
$city->location = ['longitude' => 2.3522, 'latitude' => 48.8566];
$city->save();

// Get coordinates
$latitude = $city->latitude;   // 48.8566
$longitude = $city->longitude; // 2.3522

// Find points within a radius (in kilometers)
$nearbyCities = City::nearby(48.8566, 2.3522, 10)->get();

// Find points within a circular area (alias for nearby)
$citiesInCircle = City::withinCircle(48.8566, 2.3522, 50)->get();

// Find points within a rectangular bounding box
$cities = City::withinBounds($minLat, $minLng, $maxLat, $maxLng)->get();

// Find points within a polygon
$polygon = [
    [-5.0, 42.0],  // [longitude, latitude]
    [8.0, 42.0],
    [8.0, 51.0],
    [-5.0, 51.0],
];
$citiesInPolygon = City::withinPolygon($polygon)->get();

// Calculate distance between two points (returns kilometers)
$distance = $city->distanceTo(45.7640, 4.8357); // Distance from city to Lyon
```

#### Polygon Geometry Usage

The `HasPolygon` trait provides methods for working with POLYGON geometry columns:

```php
use OiLab\OiLaravelGeo\Traits\HasPolygon;

class Department extends BaseDepartment
{
    use HasPolygon;

    protected $fillable = ['boundary', ...];
}

// Set boundary (coordinates will automatically close the polygon)
$department->boundary = [
    [2.3, 48.8],  // [longitude, latitude]
    [2.4, 48.9],
    [2.5, 48.8],
    // No need to repeat the first point, it's added automatically
];
$department->save();

// Get coordinates
$coordinates = $department->boundary_coordinates; // Returns original coordinates

// Find polygons that contain a specific point
$departments = Department::containsPoint(48.8566, 2.3522)->get();

// Find polygons that intersect with another polygon
$overlappingPolygon = [
    [0.0, 45.0],
    [10.0, 45.0],
    [10.0, 50.0],
    [0.0, 50.0],
];
$intersecting = Department::intersects($overlappingPolygon)->get();
// OR use the explicit method name
$intersecting = Department::intersectsPolygon($overlappingPolygon)->get();

// Find polygons that intersect with a rectangular bounding box
$departments = Department::intersectsBounds($minLat, $minLng, $maxLat, $maxLng)->get();
// OR use the alias
$departments = Department::intersectsRectangle($minLat, $minLng, $maxLat, $maxLng)->get();

// Find polygons that intersect with a circle
$departments = Department::intersectsCircle(48.8566, 2.3522, 100)->get();

// Calculate polygon area in square kilometers
$areaKm2 = $department->getAreaInSquareKilometers();
```

#### Query Examples

**Find all cities within 50km of Paris:**
```php
$cities = City::nearby(48.8566, 2.3522, 50)
    ->orderBy('name')
    ->get();
```

**Find all departments containing a specific GPS coordinate:**
```php
$departments = Department::containsPoint(48.8566, 2.3522)->get();
foreach ($departments as $dept) {
    echo "You are in: {$dept->name}\n";
}
```

**Find all regions that intersect with a bounding box:**
```php
// Define a bounding box around Paris area
$regions = Region::intersectsBounds(48.5, 2.0, 49.0, 3.0)->get();
```

**Complex query combining spatial and traditional filters:**
```php
$largeCitiesNearParis = City::nearby(48.8566, 2.3522, 100)
    ->where('population', '>', 100000)
    ->orderBy('population', 'desc')
    ->get();
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
