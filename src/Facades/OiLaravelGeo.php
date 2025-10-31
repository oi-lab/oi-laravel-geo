<?php

namespace OiLab\OiLaravelGeo\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string getCountryModel()
 * @method static string getRegionModel()
 * @method static string getDepartmentModel()
 * @method static string getCityModel()
 * @method static string getBoroughModel()
 * @method static string getAddressModel()
 * @method static bool isGeometryEnabled()
 *
 * @see \OiLab\OiLaravelGeo\OiLaravelGeoManager
 */
class OiLaravelGeo extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'oi-laravel-geo';
    }
}
