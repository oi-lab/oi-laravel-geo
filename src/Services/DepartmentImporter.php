<?php

namespace OiLab\OiLaravelGeo\Services;

use OiLab\OiLaravelGeo\Facades\OiLaravelGeo;

class DepartmentImporter extends AbstractGeoJsonImporter
{
    protected function importFeature(array $properties): bool
    {
        $departmentModel = OiLaravelGeo::getDepartmentModel();
        $regionModel = OiLaravelGeo::getRegionModel();

        $region = $regionModel::query()->where('code', $properties['region_code'])->first();

        if (! $region) {
            return false;
        }

        $departmentModel::query()->updateOrCreate(
            [
                'region_id' => $region->id,
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
