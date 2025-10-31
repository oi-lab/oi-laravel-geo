<?php

namespace OiLab\OiLaravelGeo\Services;

use OiLab\OiLaravelGeo\Facades\OiGeo;

class CountryImporter extends AbstractGeoJsonImporter
{
    protected function importFeature(array $properties): bool
    {
        $countryModel = OiGeo::getCountryModel();

        $countryModel::query()->updateOrCreate(
            ['code' => $properties['code']],
            [
                'name' => $properties['name'],
                'population' => $properties['population'] ?? null,
                'surface' => $properties['surface'] ?? null,
            ]
        );

        return true;
    }
}
