---
title: Address Model
description: Flexible address model with optional geographic relationships
section: models
order: 2
---

# Address Model

The Address model represents street addresses with flexible geographic relationships. It supports two modes: simple string-based city names or structured foreign key relationships to the geographic hierarchy.

## Two Modes

### Mode 1: String City (Simple)

Store city names as strings without foreign key constraints:

```php
use OiLab\Geo\Models\Address;

$address = Address::create([
    'street' => '123 Main Street',
    'city' => 'Paris', // String, not a foreign key
    'postal_code' => '75001',
]);

// Access city as a string
echo $address->city; // "Paris"
```

**Config:**
```php
'address' => [
    'include_city' => false,
    'include_country' => false,
    'include_department' => false,
    'include_region' => false,
],
```

### Mode 2: Foreign Key City (Structured)

Link addresses to the geographic hierarchy with foreign keys:

```php
use OiLab\Geo\Models\Address;
use OiLab\Geo\Models\City;

$city = City::find(1);
$address = Address::create([
    'street' => '123 Main Street',
    'city_id' => $city->id, // Foreign key to City
    'postal_code' => '75001',
]);

// Access through relationship
$address->city; // City model
$address->department; // Department model
$address->region; // Region model
```

**Config:**
```php
'address' => [
    'include_city' => true,
    'include_country' => false,
    'include_department' => false,
    'include_region' => false,
],
```

### Full Hierarchy Mode

Include all geographic relationships:

```php
use OiLab\Geo\Models\Address;

$address = Address::create([
    'street' => '123 Main Street',
    'city_id' => 1,
    'country_id' => 1,
    'department_id' => 75,
    'region_id' => 1,
    'postal_code' => '75001',
]);

// Full hierarchy access
$country = $address->country;
$region = $address->region;
$department = $address->department;
$city = $address->city;
```

**Config:**
```php
'address' => [
    'include_city' => true,
    'include_country' => true,
    'include_department' => true,
    'include_region' => true,
],
```

## Accessors & Helpers

The Address model provides convenient accessors:

### `full_address` Accessor

Get a formatted complete address string:

```php
$address = Address::find(1);
echo $address->full_address;
// Output: "123 Main Street, 75001 Paris, France"
```

The formatting depends on your configuration and model relationships.

### `city_name` Accessor

Get the city name, whether from string or relationship:

```php
// With string city
$address = Address::create(['city' => 'Paris', ...]);
echo $address->city_name; // "Paris"

// With city_id foreign key
$address = Address::create(['city_id' => 1, ...]);
echo $address->city_name; // City model name
```

## Checking Relationships

The Address model provides static methods to check which relationships are configured:

```php
use OiLab\Geo\Models\Address;

// Check if relationships are included
Address::hasCountryRelation(); // bool
Address::hasRegionRelation(); // bool
Address::hasDepartmentRelation(); // bool
Address::hasCityRelation(); // bool
```

Use these in conditionals:

```php
if (Address::hasCityRelation()) {
    $city = $address->city; // Safe access
}

if (Address::hasCountryRelation()) {
    $country = $address->country;
}
```

## Database Columns

The Address model manages the following columns based on configuration:

| Column | Type | Always Present | Conditional |
|--------|------|---|---|
| `id` | bigint unsigned | ✓ | |
| `street` | string(255) | ✓ | |
| `city` | string(100) | ✓ | Only if `include_city=false` |
| `city_id` | bigint unsigned | | Only if `include_city=true` |
| `country_id` | bigint unsigned | | Only if `include_country=true` |
| `region_id` | bigint unsigned | | Only if `include_region=true` |
| `department_id` | bigint unsigned | | Only if `include_department=true` |
| `postal_code` | string(20) | ✓ | |
| `location` | geometry (point) | | Only if `enable_geometry=true` |
| `created_at` | timestamp | ✓ | |
| `updated_at` | timestamp | ✓ | |

## Spatial Support

When geometry is enabled, addresses have an optional location point:

```php
use OiLab\Geo\Models\Address;

$address = Address::create([
    'street' => '123 Main Street',
    'city' => 'Paris',
    'postal_code' => '75001',
    'location' => [48.8566, 2.3522], // [latitude, longitude]
]);

// Access coordinates
$latitude = $address->latitude;
$longitude = $address->longitude;

// Spatial queries
$nearby = Address::nearby(48.8566, 2.3522, 5); // Within 5km
$inBounds = Address::withinBounds(48.5, 2.0, 49.0, 2.5);
```

See [HasPoint Trait](/spatial/has-point) for all spatial query methods.

## Relationships

### City Relationship

Available when `include_city=true`:

```php
$address->city; // City model
```

### Country Relationship

Available when `include_country=true`:

```php
$address->country; // Country model
```

### Region Relationship

Available when `include_region=true`:

```php
$address->region; // Region model
```

### Department Relationship

Available when `include_department=true`:

```php
$address->department; // Department model
```

## Querying Addresses

```php
use OiLab\Geo\Models\Address;

// Find by postal code
$addresses = Address::where('postal_code', '75001')->get();

// Find by city
$addresses = Address::where('city', 'Paris')->get();

// With eager loading
$addresses = Address::with('city', 'country')->get();

// Filter by geographic area
$addresses = Address::whereHas('city', function ($query) {
    $query->where('department_id', 75);
})->get();

// Spatial queries (when geometry enabled)
$nearby = Address::nearby(48.8566, 2.3522, 10)->get(); // 10km radius
```

## Validation Example

Create a form request for address creation:

```php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateAddressRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'street' => 'required|string|max:255',
            'postal_code' => 'required|string|max:20',
            'city' => 'required_if:include_city,false|string|max:100',
            'city_id' => 'required_if:include_city,true|exists:cities,id',
            'country_id' => $this->countryIdRule(),
            'region_id' => $this->regionIdRule(),
            'department_id' => $this->departmentIdRule(),
        ];
    }

    private function countryIdRule(): string
    {
        return Address::hasCountryRelation()
            ? 'required|exists:countries,id'
            : 'nullable';
    }

    private function regionIdRule(): string
    {
        return Address::hasRegionRelation()
            ? 'required|exists:regions,id'
            : 'nullable';
    }

    private function departmentIdRule(): string
    {
        return Address::hasDepartmentRelation()
            ? 'required|exists:departments,id'
            : 'nullable';
    }
}
```

## Next Steps

- Learn about [Spatial Features](/spatial) for location-based queries
- Explore [Models Overview](/models) for the full geographic hierarchy
- Check [Configuration](/configuration) to customize address behavior
