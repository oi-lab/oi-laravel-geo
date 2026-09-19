<?php

namespace OiLab\OiLaravelGeo\Observers;

use OiLab\OiLaravelGeo\Models\Address;

/**
 * Keeps at most one default address per holder.
 *
 * MySQL has no partial unique index, so `is_default` cannot be constrained
 * declaratively — the invariant is enforced on write instead.
 */
class AddressObserver
{
    public function saved(Address $address): void
    {
        if (! Address::isMorphable()) {
            return;
        }

        if (! $address->is_default) {
            return;
        }

        if ($address->addressable_type === null || $address->addressable_id === null) {
            return;
        }

        $address->newQuery()
            ->where('addressable_type', $address->addressable_type)
            ->where('addressable_id', $address->addressable_id)
            ->whereKeyNot($address->getKey())
            ->where('is_default', true)
            ->update(['is_default' => false]);
    }
}
