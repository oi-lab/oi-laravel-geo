<?php

namespace OiLab\OiLaravelGeo\Data;

use OiLab\OiLaravelGeo\Models\Address;
use Spatie\LaravelData\Data;

class AddressData extends Data
{
    public function __construct(
        public readonly int $id,
        public readonly string $street_1,
        public readonly string $postal_code,
        public readonly ?string $name = null,
        public readonly ?string $street_2 = null,
        public readonly ?string $street_3 = null,
        public readonly ?string $city = null,
        public readonly ?int $city_id = null,
        public readonly ?int $country_id = null,
        public readonly ?int $department_id = null,
        public readonly ?int $region_id = null,
        public readonly ?string $full_address = null,
    ) {}

    public static function fromModel(Address $address): self
    {
        return new self(
            id: $address->id,
            street_1: $address->street_1,
            postal_code: $address->postal_code,
            name: $address->name,
            street_2: $address->street_2,
            street_3: $address->street_3,
            city: Address::hasCityRelation() ? null : $address->getAttributes()['city'] ?? null,
            city_id: $address->city_id,
            country_id: $address->country_id,
            department_id: $address->department_id,
            region_id: $address->region_id,
            full_address: $address->full_address,
        );
    }
}
