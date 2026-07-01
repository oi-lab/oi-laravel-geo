<?php

namespace OiLab\OiLaravelGeo\Data;

use OiLab\OiLaravelGeo\Models\Department;
use Spatie\LaravelData\Data;

class DepartmentData extends Data
{
    public function __construct(
        public readonly int $id,
        public readonly int $region_id,
        public readonly string $code,
        public readonly string $name,
        public readonly ?int $population = null,
        public readonly ?int $surface = null,
    ) {}

    public static function fromModel(Department $department): self
    {
        return new self(
            id: $department->id,
            region_id: $department->region_id,
            code: $department->code,
            name: $department->name,
            population: $department->population,
            surface: $department->surface,
        );
    }
}
