<?php

namespace OiLab\OiLaravelGeo\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use OiLab\OiLaravelGeo\Facades\OiLaravelGeo;
use OiLab\OiLaravelGeo\Models\Address;

/**
 * Applied to any model that holds addresses (a member's home, a team's office,
 * a customer's sites). Requires `address_morphable` to be enabled.
 */
trait HasAddresses
{
    public function addresses(): MorphMany
    {
        return $this->morphMany(OiLaravelGeo::getAddressModel(), 'addressable');
    }

    public function defaultAddress(): ?Address
    {
        return $this->addresses()->where('is_default', true)->first();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function addAddress(array $attributes, bool $default = false): Address
    {
        return $this->addresses()->create($attributes + ['is_default' => $default]);
    }
}
