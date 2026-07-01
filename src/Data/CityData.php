<?php

namespace OiLab\OiLaravelGeo\Data;

use OiLab\OiLaravelGeo\Models\City;
use Spatie\LaravelData\Data;

class CityData extends Data
{
    public function __construct(
        public readonly int $id,
        public readonly int $department_id,
        public readonly string $identifier,
        public readonly string $code,
        public readonly string $name,
        public readonly ?int $population = null,
        public readonly ?int $surface = null,
    ) {}

    public static function fromModel(City $city): self
    {
        return new self(
            id: $city->id,
            department_id: $city->department_id,
            identifier: $city->identifier,
            code: $city->code,
            name: $city->name,
            population: $city->population,
            surface: $city->surface,
        );
    }
}
