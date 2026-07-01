<?php

namespace OiLab\OiLaravelGeo;

use OiLab\OiLaravelGeo\Models\Address;
use OiLab\OiLaravelGeo\Models\Borough;
use OiLab\OiLaravelGeo\Models\City;
use OiLab\OiLaravelGeo\Models\Country;
use OiLab\OiLaravelGeo\Models\Department;
use OiLab\OiLaravelGeo\Models\Region;

class OiLaravelGeoManager
{
    public function getCountryModel(): string
    {
        return config('oi-laravel-geo.models.country') ?? Country::class;
    }

    public function getRegionModel(): string
    {
        return config('oi-laravel-geo.models.region') ?? Region::class;
    }

    public function getDepartmentModel(): string
    {
        return config('oi-laravel-geo.models.department') ?? Department::class;
    }

    public function getCityModel(): string
    {
        return config('oi-laravel-geo.models.city') ?? City::class;
    }

    public function getBoroughModel(): string
    {
        return config('oi-laravel-geo.models.borough') ?? Borough::class;
    }

    public function getAddressModel(): string
    {
        return config('oi-laravel-geo.models.address') ?? Address::class;
    }

    public function isGeometryEnabled(): bool
    {
        return config('oi-laravel-geo.enable_geometry', false);
    }
}
