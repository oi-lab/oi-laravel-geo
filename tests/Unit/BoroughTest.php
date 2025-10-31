<?php

use OiLab\OiLaravelGeo\Models\Borough;
use OiLab\OiLaravelGeo\Models\City;
use OiLab\OiLaravelGeo\Models\Country;
use OiLab\OiLaravelGeo\Models\Department;
use OiLab\OiLaravelGeo\Models\Region;

it('can create a borough', function () {
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
        'population' => 16000,
        'surface' => 183,
    ]);

    expect($borough)
        ->city_id->toBe($city->id)
        ->code->toBe('01')
        ->name->toBe('1er Arrondissement')
        ->population->toBe(16000)
        ->surface->toBe(183);
});

it('belongs to a city', function () {
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

    expect($borough->city->id)->toBe($city->id);
});

it('requires unique code per city', function () {
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

    Borough::create([
        'city_id' => $city->id,
        'code' => '01',
        'name' => '1er Arrondissement',
    ]);

    expect(fn () => Borough::create([
        'city_id' => $city->id,
        'code' => '01',
        'name' => 'Duplicate',
    ]))->toThrow(\Illuminate\Database\QueryException::class);
});

it('cascades deletion from city', function () {
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

    $city->delete();

    expect(Borough::find($borough->id))->toBeNull();
});

it('casts population and surface to integers', function () {
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
        'population' => '16000',
        'surface' => '183',
    ]);

    expect($borough->population)->toBeInt()
        ->and($borough->surface)->toBeInt();
});

it('allows nullable population and surface', function () {
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

    expect($borough->population)->toBeNull()
        ->and($borough->surface)->toBeNull();
});
