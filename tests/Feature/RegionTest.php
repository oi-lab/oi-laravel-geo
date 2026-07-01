<?php

use Illuminate\Database\QueryException;
use OiLab\OiLaravelGeo\Models\Country;
use OiLab\OiLaravelGeo\Models\Department;
use OiLab\OiLaravelGeo\Models\Region;

it('can create a region', function () {
    $country = Country::create(['code' => 'FR', 'name' => 'France']);

    $region = Region::create([
        'country_id' => $country->id,
        'code' => 'IDF',
        'name' => 'Île-de-France',
        'population' => 12000000,
        'surface' => 12012,
    ]);

    expect($region)
        ->country_id->toBe($country->id)
        ->code->toBe('IDF')
        ->name->toBe('Île-de-France')
        ->population->toBe(12000000)
        ->surface->toBe(12012);
});

it('belongs to a country', function () {
    $country = Country::create(['code' => 'FR', 'name' => 'France']);

    $region = Region::create([
        'country_id' => $country->id,
        'code' => 'IDF',
        'name' => 'Île-de-France',
    ]);

    expect($region->country->id)->toBe($country->id);
});

it('has many departments', function () {
    $country = Country::create(['code' => 'FR', 'name' => 'France']);
    $region = Region::create([
        'country_id' => $country->id,
        'code' => 'IDF',
        'name' => 'Île-de-France',
    ]);

    $department = Department::create([
        'region_id' => $region->id,
        'code' => '75',
        'name' => 'Paris',
    ]);

    expect($region->departments)
        ->toHaveCount(1)
        ->first()->id->toBe($department->id);
});

it('requires unique code per country', function () {
    $country = Country::create(['code' => 'FR', 'name' => 'France']);

    Region::create([
        'country_id' => $country->id,
        'code' => 'IDF',
        'name' => 'Île-de-France',
    ]);

    expect(fn () => Region::create([
        'country_id' => $country->id,
        'code' => 'IDF',
        'name' => 'Duplicate',
    ]))->toThrow(QueryException::class);
});

it('cascades deletion from country', function () {
    $country = Country::create(['code' => 'FR', 'name' => 'France']);
    $region = Region::create([
        'country_id' => $country->id,
        'code' => 'IDF',
        'name' => 'Île-de-France',
    ]);

    $country->delete();

    expect(Region::find($region->id))->toBeNull();
});
