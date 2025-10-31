<?php

use OiLab\OiLaravelGeo\Models\City;
use OiLab\OiLaravelGeo\Models\Country;
use OiLab\OiLaravelGeo\Models\Department;
use OiLab\OiLaravelGeo\Models\Region;

it('can create a department', function () {
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
        'population' => 2200000,
        'surface' => 105,
    ]);

    expect($department)
        ->region_id->toBe($region->id)
        ->code->toBe('75')
        ->name->toBe('Paris')
        ->population->toBe(2200000)
        ->surface->toBe(105);
});

it('belongs to a region', function () {
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

    expect($department->region->id)->toBe($region->id);
});

it('has many cities', function () {
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

    $city = City::create([
        'department_id' => $department->id,
        'identifier' => '75056',
        'code' => '75056',
        'name' => 'Paris',
    ]);

    expect($department->cities)
        ->toHaveCount(1)
        ->first()->id->toBe($city->id);
});

it('requires unique code per region', function () {
    $country = Country::create(['code' => 'FR', 'name' => 'France']);
    $region = Region::create([
        'country_id' => $country->id,
        'code' => 'IDF',
        'name' => 'Île-de-France',
    ]);

    Department::create([
        'region_id' => $region->id,
        'code' => '75',
        'name' => 'Paris',
    ]);

    expect(fn () => Department::create([
        'region_id' => $region->id,
        'code' => '75',
        'name' => 'Duplicate',
    ]))->toThrow(\Illuminate\Database\QueryException::class);
});

it('cascades deletion from region', function () {
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

    $region->delete();

    expect(Department::find($department->id))->toBeNull();
});
