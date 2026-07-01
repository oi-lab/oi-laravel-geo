<?php

namespace OiLab\OiLaravelGeo\Services;

use OiLab\OiLaravelGeo\Facades\OiLaravelGeo;

class CityImporter extends AbstractGeoJsonImporter
{
    protected function importFeature(array $properties): bool
    {
        $cityModel = OiLaravelGeo::getCityModel();
        $departmentModel = OiLaravelGeo::getDepartmentModel();

        $department = $departmentModel::query()->where('code', $properties['department_code'])->first();

        if (! $department) {
            return false;
        }

        $cityModel::query()->updateOrCreate(
            ['identifier' => $properties['identifier']],
            [
                'department_id' => $department->id,
                'code' => $properties['code'],
                'name' => $properties['name'],
                'population' => $properties['population'] ?? null,
                'surface' => $properties['surface'] ?? null,
            ]
        );

        return true;
    }
}
