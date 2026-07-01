<?php

namespace OiLab\OiLaravelGeo\Services;

class ModelGenerator
{
    public function __construct(protected array $configuration) {}

    public function generate(): array
    {
        $models = [];

        foreach ($this->configuration['models_to_generate'] ?? [] as $modelName) {
            $models["{$modelName}.php"] = $this->generateModel($modelName);
        }

        return $models;
    }

    protected function generateModel(string $modelName): string
    {
        $traits = $this->getTraitsForModel($modelName);
        $fillable = $this->getFillableForModel($modelName);
        $traitsUse = '';
        $traitsDeclaration = '';

        if (! empty($traits)) {
            $traitsUse = "\n".implode("\n", array_map(fn ($trait) => "use {$trait};", $traits));
            $traitsDeclaration = "\n    use ".implode(', ', array_map(fn ($trait) => class_basename($trait), $traits)).";\n";
        }

        $fillableArray = '';
        if (! empty($fillable)) {
            $fillableArray = "\n    protected \$fillable = [\n";
            foreach ($fillable as $field) {
                $fillableArray .= "        '{$field}',\n";
            }
            $fillableArray .= "    ];\n";
        }

        return <<<PHP
<?php

namespace App\Models;

use OiLab\OiLaravelGeo\Models\\{$modelName} as Base{$modelName};{$traitsUse}

class {$modelName} extends Base{$modelName}
{{$traitsDeclaration}{$fillableArray}}

PHP;
    }

    /**
     * @var array<string, string>
     */
    protected const MODEL_TABLES = [
        'country' => 'countries',
        'region' => 'regions',
        'department' => 'departments',
        'city' => 'cities',
        'borough' => 'boroughs',
        'address' => 'addresses',
    ];

    protected function getTraitsForModel(string $modelName): array
    {
        $traits = [];

        if (! ($this->configuration['enable_geometry'] ?? false)) {
            return $traits;
        }

        $modelNameLower = strtolower($modelName);
        $table = self::MODEL_TABLES[$modelNameLower] ?? $modelNameLower;

        if (! in_array($table, $this->configuration['geometry_models'] ?? [])) {
            return $traits;
        }

        // Determine which traits to use based on model type
        switch ($modelNameLower) {
            case 'city':
                $traits[] = 'OiLab\OiLaravelGeo\Traits\HasPoint';
                $traits[] = 'OiLab\OiLaravelGeo\Traits\HasPolygon';
                break;
            case 'address':
                $traits[] = 'OiLab\OiLaravelGeo\Traits\HasPoint';
                break;
            case 'country':
            case 'region':
            case 'department':
            case 'borough':
                $traits[] = 'OiLab\OiLaravelGeo\Traits\HasPolygon';
                break;
        }

        return $traits;
    }

    protected function getFillableForModel(string $modelName): array
    {
        $modelNameLower = strtolower($modelName);

        // Base fillable fields (from the base model)
        $baseFillable = match ($modelNameLower) {
            'country' => ['code', 'name', 'population', 'surface'],
            'region' => ['country_id', 'code', 'name', 'population', 'surface'],
            'department' => ['region_id', 'code', 'name', 'population', 'surface'],
            'city' => ['department_id', 'identifier', 'code', 'name', 'population', 'surface'],
            'borough' => ['city_id', 'code', 'name', 'population', 'surface'],
            'address' => $this->getAddressFillable(),
            default => [],
        };

        // Add geometry fields if applicable
        if ($this->configuration['enable_geometry'] ?? false) {
            $table = self::MODEL_TABLES[$modelNameLower] ?? $modelNameLower;

            if (in_array($table, $this->configuration['geometry_models'] ?? [])) {
                switch ($modelNameLower) {
                    case 'city':
                        $baseFillable[] = 'location';
                        $baseFillable[] = 'boundary';
                        break;
                    case 'address':
                        $baseFillable[] = 'location';
                        break;
                    case 'country':
                    case 'region':
                    case 'department':
                    case 'borough':
                        $baseFillable[] = 'boundary';
                        break;
                }
            }
        }

        return $baseFillable;
    }

    protected function getAddressFillable(): array
    {
        $fillable = ['name', 'street_1', 'street_2', 'street_3', 'postal_code'];

        if ($this->configuration['address_include_city'] ?? false) {
            $fillable[] = 'city_id';
        } else {
            $fillable[] = 'city';
        }

        if ($this->configuration['address_include_country'] ?? false) {
            $fillable[] = 'country_id';
        }

        if ($this->configuration['address_include_department'] ?? false) {
            $fillable[] = 'department_id';
        }

        if ($this->configuration['address_include_region'] ?? false) {
            $fillable[] = 'region_id';
        }

        return $fillable;
    }
}
