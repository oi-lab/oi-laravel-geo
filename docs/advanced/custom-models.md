---
title: Custom Models
description: Extending default geographic models with your own logic
section: advanced
order: 1
---

# Custom Models

Every geographic model can be replaced with a custom class that extends the default one. This is the recommended way to add business logic, accessors, scopes, or additional relationships without modifying the package itself.

## Extending a model

```php
namespace App\Models;

use OiLab\OiLaravelGeo\Models\Country as BaseCountry;

class Country extends BaseCountry
{
    public function getRegionCount(): int
    {
        return $this->regions()->count();
    }

    public function scopeEuropean($query)
    {
        return $query->whereIn('code', ['FR', 'DE', 'ES', 'IT', 'PT']);
    }
}
```

## Registering in config

```php
// config/oi-laravel-geo.php
'models' => [
    'country'    => \App\Models\Country::class,
    'region'     => \App\Models\Region::class,
    'department' => \App\Models\Department::class,
    'city'       => \App\Models\City::class,
    'borough'    => \App\Models\Borough::class,
    'address'    => \App\Models\Address::class,
],
```

Leave any entry as `null` to keep using the default package model.

## Using the manager facade

When building generic code that should respect the configured models, use the facade instead of hard-coding class names:

```php
use OiLab\OiLaravelGeo\Facades\OiLaravelGeo;

$countryClass = OiLaravelGeo::getCountryModel(); // Returns configured class
$countries = $countryClass::all();
```

Available resolver methods:
```php
OiLaravelGeo::getCountryModel(): string
OiLaravelGeo::getRegionModel(): string
OiLaravelGeo::getDepartmentModel(): string
OiLaravelGeo::getCityModel(): string
OiLaravelGeo::getBoroughModel(): string
OiLaravelGeo::getAddressModel(): string
OiLaravelGeo::isGeometryEnabled(): bool
```

## Adding geometry traits to custom models

If geometry is enabled in config, add the appropriate trait to your custom model:

```php
namespace App\Models;

use OiLab\OiLaravelGeo\Models\City as BaseCity;
use OiLab\OiLaravelGeo\Traits\HasPoint;

class City extends BaseCity
{
    use HasPoint;

    protected $fillable = [
        'department_id', 'identifier', 'code', 'name',
        'population', 'surface', 'location',
    ];

    public function getFormattedLocation(): string
    {
        return sprintf('%.4f°N, %.4f°E', $this->latitude, $this->longitude);
    }
}
```
