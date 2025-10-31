<?php

namespace OiLab\OiLaravelGeo\Services;

use OiLab\OiLaravelGeo\Facades\OiGeo;

class BoroughImporter extends AbstractGeoJsonImporter
{
    protected function importFeature(array $properties): bool
    {
        $boroughModel = OiGeo::getBoroughModel();
        $cityModel = OiGeo::getCityModel();

        $city = $cityModel::query()->where('identifier', $properties['city_identifier'])->first();

        if (!$city) {
            return false;
        }

        $boroughModel::query()->updateOrCreate(
            [
                'city_id' => $city->id,
                'code' => $properties['code'],
            ],
            [
                'name' => $properties['name'],
                'population' => $properties['population'] ?? null,
                'surface' => $properties['surface'] ?? null,
            ]
        );

        return true;
    }
}
