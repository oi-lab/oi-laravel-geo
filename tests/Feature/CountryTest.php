<?php

use Illuminate\Database\QueryException;
use OiLab\OiLaravelGeo\Models\Country;
use OiLab\OiLaravelGeo\Models\Region;

it('can create a country', function () {
    $country = Country::create([
        'code' => 'FR',
        'name' => 'France',
        'population' => 67000000,
        'surface' => 551695,
    ]);

    expect($country)
        ->code->toBe('FR')
        ->name->toBe('France')
        ->population->toBe(67000000)
        ->surface->toBe(551695);
});

it('requires unique country code', function () {
    Country::create([
        'code' => 'FR',
        'name' => 'France',
    ]);

    expect(fn () => Country::create([
        'code' => 'FR',
        'name' => 'France Duplicate',
    ]))->toThrow(QueryException::class);
});

it('has many regions', function () {
    $country = Country::create([
        'code' => 'FR',
        'name' => 'France',
    ]);

    $region = Region::create([
        'country_id' => $country->id,
        'code' => 'IDF',
        'name' => 'Île-de-France',
    ]);

    expect($country->regions)
        ->toHaveCount(1)
        ->first()->id->toBe($region->id);
});

it('casts population and surface to integers', function () {
    $country = Country::create([
        'code' => 'FR',
        'name' => 'France',
        'population' => '67000000',
        'surface' => '551695',
    ]);

    expect($country->population)->toBeInt()
        ->and($country->surface)->toBeInt();
});

it('allows nullable population and surface', function () {
    $country = Country::create([
        'code' => 'FR',
        'name' => 'France',
    ]);

    expect($country->population)->toBeNull()
        ->and($country->surface)->toBeNull();
});
