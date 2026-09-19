<?php

namespace OiLab\OiLaravelGeo\Data;

use Carbon\CarbonInterface;
use OiLab\OiLaravelGeo\Models\Address;
use Spatie\LaravelData\Data;

class AddressData extends Data
{
    public function __construct(
        public readonly int|string $id,
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
        public readonly ?string $addressable_type = null,
        public readonly int|string|null $addressable_id = null,
        public readonly bool $is_default = false,
        public readonly ?float $latitude = null,
        public readonly ?float $longitude = null,
        public readonly ?string $geocoded_label = null,
        public readonly ?float $geocoding_score = null,
        public readonly ?string $ban_id = null,
        public readonly ?CarbonInterface $geocoded_at = null,
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
            addressable_type: $address->addressable_type,
            addressable_id: $address->addressable_id,
            is_default: (bool) $address->is_default,
            latitude: $address->latitude !== null ? (float) $address->latitude : null,
            longitude: $address->longitude !== null ? (float) $address->longitude : null,
            geocoded_label: $address->geocoded_label,
            geocoding_score: $address->geocoding_score !== null ? (float) $address->geocoding_score : null,
            ban_id: $address->ban_id,
            geocoded_at: $address->geocoded_at,
        );
    }
}
