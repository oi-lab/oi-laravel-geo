<?php

use Illuminate\Support\Carbon;
use OiLab\OiLaravelGeo\Data\AddressData;
use OiLab\OiLaravelGeo\Models\Address;

it('leaves every geocoding column null on a fresh address', function () {
    $address = Address::create([
        'street_1' => '5 Avenue Anatole France',
        'city' => 'Paris',
        'postal_code' => '75007',
    ]);

    expect($address->latitude)->toBeNull()
        ->and($address->longitude)->toBeNull()
        ->and($address->geocoded_label)->toBeNull()
        ->and($address->geocoding_score)->toBeNull()
        ->and($address->ban_id)->toBeNull()
        ->and($address->geocoded_at)->toBeNull()
        ->and($address->isGeocoded())->toBeFalse();
});

it('is not geocoded when only one coordinate is set', function () {
    $address = Address::create([
        'street_1' => '5 Avenue Anatole France',
        'city' => 'Paris',
        'postal_code' => '75007',
        'latitude' => 48.8583701,
    ]);

    expect($address->isGeocoded())->toBeFalse();
});

it('is geocoded once both coordinates are set', function () {
    $address = Address::create([
        'street_1' => '5 Avenue Anatole France',
        'city' => 'Paris',
        'postal_code' => '75007',
        'latitude' => 48.8583701,
        'longitude' => 2.2944813,
        'geocoded_label' => '5 Avenue Anatole France 75007 Paris',
        'geocoding_score' => 0.976,
        'ban_id' => '75107_0463_00005',
        'geocoded_at' => now(),
    ]);

    expect($address->isGeocoded())->toBeTrue()
        ->and((float) $address->latitude)->toBe(48.8583701)
        ->and((float) $address->longitude)->toBe(2.2944813)
        ->and((float) $address->geocoding_score)->toBe(0.976)
        ->and($address->ban_id)->toBe('75107_0463_00005')
        ->and($address->geocoded_at)->toBeInstanceOf(Carbon::class);
});

it('rounds coordinates to seven decimals and the score to three', function () {
    $address = Address::create([
        'street_1' => '5 Avenue Anatole France',
        'city' => 'Paris',
        'postal_code' => '75007',
        'latitude' => 48.85837012345,
        'longitude' => 2.29448131234,
        'geocoding_score' => 0.9764321,
    ]);

    expect($address->latitude)->toBe('48.8583701')
        ->and($address->longitude)->toBe('2.2944813')
        ->and($address->geocoding_score)->toBe('0.976');
});

it('carries the geocoding fields into AddressData', function () {
    $address = Address::create([
        'street_1' => '5 Avenue Anatole France',
        'city' => 'Paris',
        'postal_code' => '75007',
        'latitude' => 48.8583701,
        'longitude' => 2.2944813,
        'geocoded_label' => '5 Avenue Anatole France 75007 Paris',
        'geocoding_score' => 0.976,
        'ban_id' => '75107_0463_00005',
        'geocoded_at' => now(),
    ]);

    $data = $address->toData();

    expect($data->latitude)->toBe(48.8583701)
        ->and($data->longitude)->toBe(2.2944813)
        ->and($data->geocoded_label)->toBe('5 Avenue Anatole France 75007 Paris')
        ->and($data->geocoding_score)->toBe(0.976)
        ->and($data->ban_id)->toBe('75107_0463_00005')
        ->and($data->geocoded_at)->not->toBeNull();
});

it('stays constructible without any of the new fields', function () {
    $data = new AddressData(
        id: 1,
        street_1: '5 Avenue Anatole France',
        postal_code: '75007',
    );

    expect($data->addressable_type)->toBeNull()
        ->and($data->addressable_id)->toBeNull()
        ->and($data->is_default)->toBeFalse()
        ->and($data->latitude)->toBeNull()
        ->and($data->geocoded_at)->toBeNull()
        ->and($data->toArray())->toHaveKey('street_1');
});
