<?php

use Illuminate\Support\Str;
use OiLab\OiLaravelGeo\Models\Address;
use OiLab\OiLaravelGeo\Tests\Fixtures\Models\TestUser;

it('uses an auto-incrementing integer key by default', function () {
    $address = Address::create([
        'street_1' => '5 Avenue Anatole France',
        'city' => 'Paris',
        'postal_code' => '75007',
    ]);

    expect(Address::usesUlidKey())->toBeFalse()
        ->and($address->getIncrementing())->toBeTrue()
        ->and($address->getKeyType())->toBe('int')
        ->and($address->id)->toBeInt();
});

describe('with address_key_type = ulid', function () {
    beforeEach(function () {
        config([
            'oi-laravel-geo.address_key_type' => 'ulid',
            'oi-laravel-geo.tables.addresses' => 'ulid_addresses',
        ]);
    });

    it('generates a ulid primary key on create', function () {
        $address = Address::create([
            'street_1' => '5 Avenue Anatole France',
            'city' => 'Paris',
            'postal_code' => '75007',
        ]);

        expect($address->getIncrementing())->toBeFalse()
            ->and($address->getKeyType())->toBe('string')
            ->and($address->id)->toBeString()->toHaveLength(26)
            ->and(Address::find($address->id)?->street_1)->toBe('5 Avenue Anatole France');
    });

    it('keeps an explicitly provided key', function () {
        $ulid = (string) Str::ulid();

        $address = new Address([
            'street_1' => '5 Avenue Anatole France',
            'city' => 'Paris',
            'postal_code' => '75007',
        ]);
        $address->id = $ulid;
        $address->save();

        expect($address->id)->toBe($ulid);
    });

    it('carries the ulid key into AddressData', function () {
        $address = Address::create([
            'street_1' => '5 Avenue Anatole France',
            'city' => 'Paris',
            'postal_code' => '75007',
        ]);

        expect($address->toData()->id)->toBe($address->id)->toBeString();
    });

    it('still resolves addresses for a bigint holder', function () {
        config(['oi-laravel-geo.address_morphable' => true]);

        $user = TestUser::create(['name' => 'Ada']);
        $address = $user->addAddress([
            'street_1' => '5 Avenue Anatole France',
            'city' => 'Paris',
            'postal_code' => '75007',
        ]);

        expect($address->id)->toBeString()->toHaveLength(26)
            ->and($user->addresses()->pluck('id')->all())->toBe([$address->id]);
    });
});
