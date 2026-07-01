<?php

namespace OiLab\OiLaravelGeo\Data;

use OiLab\OiLaravelGeo\Models\Borough;
use Spatie\LaravelData\Data;

class BoroughData extends Data
{
    public function __construct(
        public readonly int $id,
        public readonly int $city_id,
        public readonly string $code,
        public readonly string $name,
        public readonly ?int $population = null,
        public readonly ?int $surface = null,
    ) {}

    public static function fromModel(Borough $borough): self
    {
        return new self(
            id: $borough->id,
            city_id: $borough->city_id,
            code: $borough->code,
            name: $borough->name,
            population: $borough->population,
            surface: $borough->surface,
        );
    }
}
