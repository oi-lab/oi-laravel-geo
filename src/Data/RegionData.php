<?php

namespace OiLab\OiLaravelGeo\Data;

use OiLab\OiLaravelGeo\Models\Region;
use Spatie\LaravelData\Data;

class RegionData extends Data
{
    public function __construct(
        public readonly int $id,
        public readonly int $country_id,
        public readonly string $code,
        public readonly string $name,
        public readonly ?int $population = null,
        public readonly ?int $surface = null,
    ) {}

    public static function fromModel(Region $region): self
    {
        return new self(
            id: $region->id,
            country_id: $region->country_id,
            code: $region->code,
            name: $region->name,
            population: $region->population,
            surface: $region->surface,
        );
    }
}
