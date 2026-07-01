<?php

use Illuminate\Database\QueryException;
use OiLab\OiLaravelGeo\Models\Borough;
use OiLab\OiLaravelGeo\Models\City;
use OiLab\OiLaravelGeo\Models\Country;
use OiLab\OiLaravelGeo\Models\Department;
use OiLab\OiLaravelGeo\Models\Region;

it('can create a city', function () {
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
        'population' => 2200000,
        'surface' => 105,
    ]);

    expect($city)
        ->department_id->toBe($department->id)
        ->identifier->toBe('75056')
        ->code->toBe('75056')
        ->name->toBe('Paris')
        ->population->toBe(2200000)
        ->surface->toBe(105);
});

it('belongs to a department', function () {
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

    expect($city->department->id)->toBe($department->id);
});

it('requires unique identifier', function () {
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

    City::create([
        'department_id' => $department->id,
        'identifier' => '75056',
        'code' => '75056',
        'name' => 'Paris',
    ]);

    expect(fn () => City::create([
        'department_id' => $department->id,
        'identifier' => '75056',
        'code' => '75057',
        'name' => 'Duplicate',
    ]))->toThrow(QueryException::class);
});

it('requires unique code per department', function () {
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

    City::create([
        'department_id' => $department->id,
        'identifier' => '75056',
        'code' => '001',
        'name' => 'Paris',
    ]);

    expect(fn () => City::create([
        'department_id' => $department->id,
        'identifier' => '75057',
        'code' => '001',
        'name' => 'Duplicate',
    ]))->toThrow(QueryException::class);
});

it('cascades deletion from department', function () {
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

    $department->delete();

    expect(City::find($city->id))->toBeNull();
});

it('has many boroughs', function () {
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

    $borough = Borough::create([
        'city_id' => $city->id,
        'code' => '01',
        'name' => '1er Arrondissement',
    ]);

    expect($city->boroughs)
        ->toHaveCount(1)
        ->first()->id->toBe($borough->id);
});
