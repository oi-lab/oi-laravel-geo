---
title: Models Overview
description: Geographic hierarchy and Eloquent relationships
section: models
order: 1
---

# Models Overview

OI Laravel Geo provides six Eloquent models representing the geographic hierarchy. Each model has relationships to its parent and children, allowing you to traverse the hierarchy in both directions.

## The Five-Level Hierarchy

The geographic hierarchy flows from broad to specific:

```
Country
  ├── Region
  │     ├── Department
  │     │     ├── City
  │     │     │     └── Borough
  │     │     │           └── Address (optional FK)
```

## Model Relationships

### Country

The top-level geographic entity.

```php
use OiLab\Geo\Models\Country;

$country = Country::find(1);

// Has many regions
$regions = $country->regions; // Collection<Region>

// Navigate to children
$departments = $country->departments;
$cities = $country->cities;
$boroughs = $country->boroughs;
```

### Region

A subdivision within a country.

```php
use OiLab\Geo\Models\Region;

$region = Region::find(1);

// Belongs to country
$country = $region->country;

// Has many departments
$departments = $region->departments;

// Navigate to children
$cities = $region->cities;
$boroughs = $region->boroughs;

// Navigate up
$country = $region->country;
```

### Department

A subdivision within a region.

```php
use OiLab\Geo\Models\Department;

$department = Department::find(1);

// Belongs to region and country
$region = $department->region;
$country = $department->country;

// Has many cities
$cities = $department->cities;

// Navigate down
$boroughs = $department->boroughs;

// Navigate up the hierarchy
$region = $department->region;
$country = $department->country;
```

### City

An urban center within a department.

```php
use OiLab\Geo\Models\City;

$city = City::find(1);

// Belongs to parent hierarchy
$department = $city->department;
$region = $city->region;
$country = $city->country;

// Has many boroughs
$boroughs = $city->boroughs;

// Has many addresses (if configured)
$addresses = $city->addresses;

// Optional: location point geometry
if ($city->hasLocation()) {
    $latitude = $city->latitude;
    $longitude = $city->longitude;
}
```

### Borough

A neighborhood or subdivision within a city.

```php
use OiLab\Geo\Models\Borough;

$borough = Borough::find(1);

// Belongs to parent hierarchy
$city = $borough->city;
$department = $borough->department;
$region = $borough->region;
$country = $borough->country;

// Has many addresses (if configured)
$addresses = $borough->addresses;
```

### Address

Street addresses with optional relationships to the geographic hierarchy.

```php
use OiLab\Geo\Models\Address;

$address = Address::find(1);

// Optional: relationships (based on config)
$city = $address->city; // if include_city is true
$country = $address->country; // if include_country is true
$department = $address->department; // if include_department is true
$region = $address->region; // if include_region is true

// Full address string
$fullAddress = $address->full_address;

// City name (string or relationship)
$cityName = $address->city_name;

// Optional: location point geometry
if ($address->hasLocation()) {
    $latitude = $address->latitude;
    $longitude = $address->longitude;
}
```

## Cascade Deletes

All models are configured with cascade deletes. Deleting a parent automatically deletes all children:

```php
// Deleting a country deletes all regions, departments, cities, boroughs, and addresses within
$country->delete(); // Cascades to all descendants

// Deleting a city deletes all boroughs and addresses within
$city->delete();
```

## Eager Loading

Always eager load relationships to avoid N+1 queries:

```php
// Good: eager load
$cities = City::with('department', 'region', 'country')->get();

// Bad: N+1 queries
$cities = City::all();
foreach ($cities as $city) {
    echo $city->department->name; // Query executed for each city
}
```

## Finding Records

Use standard Eloquent methods with relationships:

```php
use OiLab\Geo\Models\Country;

// Find by ID
$country = Country::find(1);

// Find with relationships
$country = Country::with('regions.departments.cities')->find(1);

// Query with constraints
$frenchCities = City::whereHas('region', function ($query) {
    $query->where('country_id', 1);
})->get();
```

## Filtering the Hierarchy

Query across the hierarchy:

```php
use OiLab\Geo\Models\City;

// Find all cities in a specific country
$citiesInFrance = City::whereHas('country', function ($query) {
    $query->where('code', 'FR');
})->get();

// Find cities in a specific department
$citiesInParis = City::where('department_id', 75)->get();

// Find cities with addresses
$citiesWithAddresses = City::has('addresses')->get();
```

## Next Steps

- Learn about the [Address Model](/models/address) and its flexible configuration
- Explore [Spatial Features](/spatial) for location-based queries
- Check [Custom Models](/advanced/custom-models) to extend the default models
