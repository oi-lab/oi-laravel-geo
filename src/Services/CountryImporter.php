<?php

namespace OiLab\OiLaravelGeo\Services;

use OiLab\OiLaravelGeo\Facades\OiLaravelGeo;

class CountryImporter extends AbstractGeoJsonImporter
{
    protected function importFeature(array $properties): bool
    {
        $countryModel = OiLaravelGeo::getCountryModel();

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
