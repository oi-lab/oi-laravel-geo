<?php

namespace OiLab\OiLaravelGeo\Services;

use OiLab\OiLaravelGeo\Facades\OiLaravelGeo;

class BoroughImporter extends AbstractGeoJsonImporter
{
    protected function importFeature(array $properties): bool
    {
        $boroughModel = OiLaravelGeo::getBoroughModel();
        $cityModel = OiLaravelGeo::getCityModel();

        $city = $cityModel::query()->where('identifier', $properties['city_identifier'])->first();

        if (! $city) {
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
