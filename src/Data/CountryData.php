<?php

namespace OiLab\OiLaravelGeo\Data;

use OiLab\OiLaravelGeo\Models\Country;
use Spatie\LaravelData\Data;

class CountryData extends Data
{
    public function __construct(
        public readonly int $id,
        public readonly string $code,
        public readonly string $name,
        public readonly ?int $population = null,
        public readonly ?int $surface = null,
    ) {}

    public static function fromModel(Country $country): self
    {
        return new self(
            id: $country->id,
            code: $country->code,
            name: $country->name,
            population: $country->population,
            surface: $country->surface,
        );
    }
}
