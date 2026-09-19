<?php

use OiLab\OiLaravelGeo\Models\Address;
use OiLab\OiLaravelGeo\Tests\Fixtures\Models\TestEntity;
use OiLab\OiLaravelGeo\Tests\Fixtures\Models\TestUser;

beforeEach(function () {
    config(['oi-laravel-geo.address_morphable' => true]);
});

it('attaches an address to a holder through the morph', function () {
    $user = TestUser::create(['name' => 'Ada']);

    $address = $user->addAddress([
        'street_1' => '5 Avenue Anatole France',
        'city' => 'Paris',
        'postal_code' => '75007',
    ]);

    expect($address->addressable_type)->toBe(TestUser::class)
        ->and((string) $address->addressable_id)->toBe((string) $user->id)
        ->and($address->addressable)->toBeInstanceOf(TestUser::class)
        ->and($address->addressable->is($user))->toBeTrue();
});

it('keeps holders with different key types apart in the same table', function () {
    $user = TestUser::create(['name' => 'Ada']);
    $entity = TestEntity::create(['name' => 'Acme HQ']);

    $userAddress = $user->addAddress([
        'street_1' => '5 Avenue Anatole France',
        'city' => 'Paris',
        'postal_code' => '75007',
    ]);

    $entityAddress = $entity->addAddress([
        'street_1' => '1 Place de la Comédie',
        'city' => 'Montpellier',
        'postal_code' => '34000',
    ]);

    expect($user->addresses()->pluck('id')->all())->toBe([$userAddress->id])
        ->and($entity->addresses()->pluck('id')->all())->toBe([$entityAddress->id]);

    expect($entity->id)->toBeString()->toHaveLength(26)
        ->and($user->id)->toBeInt();
});

it('does not leak addresses between a bigint holder and a ulid holder', function () {
    $user = TestUser::create(['name' => 'Ada']);
    $entity = TestEntity::create(['name' => 'Acme HQ']);

    $user->addAddress(['street_1' => 'A', 'city' => 'Paris', 'postal_code' => '75007']);
    $user->addAddress(['street_1' => 'B', 'city' => 'Paris', 'postal_code' => '75008']);
    $entity->addAddress(['street_1' => 'C', 'city' => 'Montpellier', 'postal_code' => '34000']);

    expect($user->addresses()->count())->toBe(2)
        ->and($entity->addresses()->count())->toBe(1)
        ->and($entity->addresses()->pluck('street_1')->all())->toBe(['C']);
});

it('marks a first address as default when asked', function () {
    $user = TestUser::create(['name' => 'Ada']);

    $address = $user->addAddress([
        'street_1' => '5 Avenue Anatole France',
        'city' => 'Paris',
        'postal_code' => '75007',
    ], default: true);

    expect($address->is_default)->toBeTrue()
        ->and($user->defaultAddress()?->id)->toBe($address->id);
});

it('creates non-default addresses by default', function () {
    $user = TestUser::create(['name' => 'Ada']);

    $address = $user->addAddress([
        'street_1' => '5 Avenue Anatole France',
        'city' => 'Paris',
        'postal_code' => '75007',
    ]);

    expect($address->is_default)->toBeFalse()
        ->and($user->defaultAddress())->toBeNull();
});

it('unsets the previous default when a second default is set on the same holder', function () {
    $user = TestUser::create(['name' => 'Ada']);

    $first = $user->addAddress(['street_1' => 'A', 'city' => 'Paris', 'postal_code' => '75007'], default: true);
    $second = $user->addAddress(['street_1' => 'B', 'city' => 'Paris', 'postal_code' => '75008'], default: true);

    expect($first->fresh()->is_default)->toBeFalse()
        ->and($second->fresh()->is_default)->toBeTrue()
        ->and($user->defaultAddress()?->id)->toBe($second->id);
});

it('leaves another holder default untouched', function () {
    $user = TestUser::create(['name' => 'Ada']);
    $entity = TestEntity::create(['name' => 'Acme HQ']);

    $userDefault = $user->addAddress(['street_1' => 'A', 'city' => 'Paris', 'postal_code' => '75007'], default: true);
    $entityDefault = $entity->addAddress(['street_1' => 'C', 'city' => 'Montpellier', 'postal_code' => '34000'], default: true);

    expect($userDefault->fresh()->is_default)->toBeTrue()
        ->and($entityDefault->fresh()->is_default)->toBeTrue();
});

it('does not touch other defaults when morph support is disabled', function () {
    $user = TestUser::create(['name' => 'Ada']);

    $first = $user->addAddress(['street_1' => 'A', 'city' => 'Paris', 'postal_code' => '75007'], default: true);

    config(['oi-laravel-geo.address_morphable' => false]);

    $second = $user->addAddress(['street_1' => 'B', 'city' => 'Paris', 'postal_code' => '75008'], default: true);

    expect($first->fresh()->is_default)->toBeTrue()
        ->and($second->fresh()->is_default)->toBeTrue();
});

it('exposes the morph fields through AddressData', function () {
    $user = TestUser::create(['name' => 'Ada']);

    $data = $user->addAddress([
        'street_1' => '5 Avenue Anatole France',
        'city' => 'Paris',
        'postal_code' => '75007',
    ], default: true)->toData();

    expect($data->addressable_type)->toBe(TestUser::class)
        ->and((string) $data->addressable_id)->toBe((string) $user->id)
        ->and($data->is_default)->toBeTrue();
});

it('leaves morph fields null on a standalone address', function () {
    $address = Address::create([
        'street_1' => '5 Avenue Anatole France',
        'city' => 'Paris',
        'postal_code' => '75007',
    ]);

    expect($address->addressable_type)->toBeNull()
        ->and($address->addressable_id)->toBeNull()
        ->and($address->addressable)->toBeNull()
        ->and($address->fresh()->is_default)->toBeFalse();
});
