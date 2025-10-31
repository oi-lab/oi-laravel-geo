<?php

namespace OiLab\OiLaravelGeo;

class OiLaravelGeoManager
{
    public function getCountryModel(): string
    {
        return config('oi-laravel-geo.models.country') ?? \OiLab\OiLaravelGeo\Models\Country::class;
    }

    public function getRegionModel(): string
    {
        return config('oi-laravel-geo.models.region') ?? \OiLab\OiLaravelGeo\Models\Region::class;
    }

    public function getDepartmentModel(): string
    {
        return config('oi-laravel-geo.models.department') ?? \OiLab\OiLaravelGeo\Models\Department::class;
    }

    public function getCityModel(): string
    {
        return config('oi-laravel-geo.models.city') ?? \OiLab\OiLaravelGeo\Models\City::class;
    }

    public function getBoroughModel(): string
    {
        return config('oi-laravel-geo.models.borough') ?? \OiLab\OiLaravelGeo\Models\Borough::class;
    }

    public function getAddressModel(): string
    {
        return config('oi-laravel-geo.models.address') ?? \OiLab\OiLaravelGeo\Models\Address::class;
    }

    public function isGeometryEnabled(): bool
    {
        return config('oi-laravel-geo.enable_geometry', false);
    }
}
