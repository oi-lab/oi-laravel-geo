<?php

namespace OiLab\OiLaravelGeo\Services;

use OiLab\OiLaravelGeo\Facades\OiGeo;

class RegionImporter extends AbstractGeoJsonImporter
{
    protected function importFeature(array $properties): bool
    {
        $regionModel = OiGeo::getRegionModel();
        $countryModel = OiGeo::getCountryModel();

        $country = $countryModel::query()->where('code', $properties['country_code'])->first();

        if (! $country) {
            return false;
        }

        $regionModel::query()->updateOrCreate(
            [
                'country_id' => $country->id,
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
