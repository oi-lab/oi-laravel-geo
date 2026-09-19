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

// Check which opt-in capabilities are on
Address::isMorphable();          // bool — address_morphable
Address::usesUlidKey();          // bool — address_key_type === 'ulid'
Address::hasGeocodingColumns();  // bool — address_geocoding
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
| `id` | bigint unsigned / ulid | ✓ | `ulid('id')` if `address_key_type='ulid'` |
| `street` | string(255) | ✓ | |
| `city` | string(100) | ✓ | Only if `include_city=false` |
| `city_id` | bigint unsigned | | Only if `include_city=true` |
| `country_id` | bigint unsigned | | Only if `include_country=true` |
| `region_id` | bigint unsigned | | Only if `include_region=true` |
| `department_id` | bigint unsigned | | Only if `include_department=true` |
| `postal_code` | string(20) | ✓ | |
| `location` | geometry (point) | | Only if `enable_geometry=true` |
| `addressable_type` | string(255) | | Only if `address_morphable=true` |
| `addressable_id` | string(36) | | Only if `address_morphable=true` |
| `is_default` | boolean | | Only if `address_morphable=true` |
| `latitude` | decimal(10,7) | | Only if `address_geocoding=true` |
| `longitude` | decimal(10,7) | | Only if `address_geocoding=true` |
| `geocoded_label` | string(255) | | Only if `address_geocoding=true` |
| `geocoding_score` | decimal(4,3) | | Only if `address_geocoding=true` |
| `ban_id` | string(32) | | Only if `address_geocoding=true` |
| `geocoded_at` | timestamp | | Only if `address_geocoding=true` |
| `created_at` | timestamp | ✓ | |
| `updated_at` | timestamp | ✓ | |

## Polymorphic Addresses

Enable `address_morphable` to attach an address to any model — a member's home,
a team's office, a customer's sites:

```php
use OiLab\OiLaravelGeo\Concerns\HasAddresses;

class Team extends Model
{
    use HasAddresses;
}
```

The trait provides three methods:

```php
$team->addresses();        // MorphMany
$team->defaultAddress();   // ?Address — the one flagged is_default, or null
$team->addAddress([
    'street_1' => '1 Place de la Comédie',
    'city' => 'Montpellier',
    'postal_code' => '34000',
], default: true);
```

And the address points back through a `morphTo()`:

```php
$address->addressable;         // the holder model
$address->addressable_type;    // its class name
```

### Why `string(36)` and not `morphs()`

`addressable_id` is declared as an explicit `string(36)` column, not through
`$table->morphs()` or `$table->ulidMorphs()`.

Address holders legitimately have different key types in the same application:
a `User` and a `Team` on an auto-incrementing bigint, an `Entity` on a ULID. A
morph helper commits the column to one type and breaks the others. A
36-character string holds all of them — a bigint, a ULID (26 characters) and a
UUID (36 characters) — and Eloquent compares them correctly in both
directions.

The `['addressable_type', 'addressable_id']` index is declared by hand for the
same reason. Do not "simplify" this back to `morphs()`.

### One default per holder

`is_default` is added together with the morph columns: the flag only means
something relative to a holder.

MySQL has no partial unique index, so the constraint cannot be declarative. It
is enforced on write by `OiLab\OiLaravelGeo\Observers\AddressObserver`, which
flips the holder's other defaults to `false` whenever an address is saved as
the default. Other holders are never touched.

## ULID Primary Key

Set `address_key_type` to `'ulid'` to swap `$table->id()` for
`$table->ulid('id')->primary()` in the generated migration. The default `'id'`
keeps the auto-incrementing bigint.

```php
config(['oi-laravel-geo.address_key_type' => 'ulid']);

$address = Address::create([...]);
$address->id;                  // "01JN4Z6T8QK3W2F5RQY7X9B0CD"
$address->getIncrementing();   // false
$address->getKeyType();        // "string"
```

The model does not use the `HasUlids` trait: that trait decides the key type
statically, while here it depends on the configuration, and a conditional
`use` does not exist in PHP. `getKeyType()`, `getIncrementing()` and a
`creating` hook that fills an empty key with `Str::ulid()` are implemented by
hand in `Address` instead.

## Geocoding Columns

Enable `address_geocoding` to add six nullable columns:

| Column | Type |
|--------|------|
| `latitude` | `decimal(10,7)` |
| `longitude` | `decimal(10,7)` |
| `geocoded_label` | `string(255)` |
| `geocoding_score` | `decimal(4,3)` |
| `ban_id` | `string(32)` |
| `geocoded_at` | `timestamp` |

```php
$address->isGeocoded();     // true once latitude and longitude are both set
$address->geocoding_score;  // "0.976"
$address->toData();         // AddressData carries every field
```

### These do not replace `enable_geometry`

The two mechanisms are independent and can be used together, separately, or
not at all.

`enable_geometry` stores a `Point` — a native geometry type on PostgreSQL, a
JSON payload on MySQL and SQLite — which is what the spatial scopes query.

The geocoding columns store readable decimals, for rounding a cache key, for
comparing, for displaying, **and** metadata a `Point` cannot carry: the
provider's confidence score and the BAN identifier.

### The package does not geocode

`oi-laravel-geo` carries the columns and the contract, never the network call.
There is no dependency on any geocoding service. Filling these fields is the
application's job.

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
