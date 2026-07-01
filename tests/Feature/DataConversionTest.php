<?php

use OiLab\OiLaravelGeo\Data\AddressData;
use OiLab\OiLaravelGeo\Data\BoroughData;
use OiLab\OiLaravelGeo\Data\CityData;
use OiLab\OiLaravelGeo\Data\CountryData;
use OiLab\OiLaravelGeo\Data\DepartmentData;
use OiLab\OiLaravelGeo\Data\RegionData;
use OiLab\OiLaravelGeo\Models\Address;
use OiLab\OiLaravelGeo\Models\Borough;
use OiLab\OiLaravelGeo\Models\City;
use OiLab\OiLaravelGeo\Models\Country;
use OiLab\OiLaravelGeo\Models\Department;
use OiLab\OiLaravelGeo\Models\Region;

function createGeoHierarchy(): array
{
    $country = Country::create(['code' => 'FR', 'name' => 'France', 'population' => 68000000]);
    $region = Region::create(['country_id' => $country->id, 'code' => '76', 'name' => 'Occitanie']);
    $department = Department::create(['region_id' => $region->id, 'code' => '34', 'name' => 'Hérault']);
    $city = City::create([
        'department_id' => $department->id,
        'identifier' => '34172',
        'code' => '34172',
        'name' => 'Montpellier',
        'population' => 295542,
    ]);
    $borough = Borough::create(['city_id' => $city->id, 'code' => 'MTP1', 'name' => 'Centre']);

    return [$country, $region, $department, $city, $borough];
}

it('converts every geo model to its Data object', function () {
    [$country, $region, $department, $city, $borough] = createGeoHierarchy();

    expect($country->toData())->toBeInstanceOf(CountryData::class)
        ->and($country->toData()->code)->toBe('FR')
        ->and($country->toData()->population)->toBe(68000000)
        ->and($region->toData())->toBeInstanceOf(RegionData::class)
        ->and($region->toData()->country_id)->toBe($country->id)
        ->and($department->toData())->toBeInstanceOf(DepartmentData::class)
        ->and($department->toData()->code)->toBe('34')
        ->and($city->toData())->toBeInstanceOf(CityData::class)
        ->and($city->toData()->identifier)->toBe('34172')
        ->and($borough->toData())->toBeInstanceOf(BoroughData::class)
        ->and($borough->toData()->city_id)->toBe($city->id);
});

it('converts an address with string city to Data', function () {
    $address = Address::create([
        'street_1' => '5 Avenue Anatole France',
        'city' => 'Paris',
        'postal_code' => '75007',
    ]);

    $data = $address->toData();

    expect($data)->toBeInstanceOf(AddressData::class)
        ->and($data->street_1)->toBe('5 Avenue Anatole France')
        ->and($data->city)->toBe('Paris')
        ->and($data->city_id)->toBeNull()
        ->and($data->full_address)->toContain('Paris');
});

it('converts an address with city relation to Data', function () {
    [, , , $city] = createGeoHierarchy();

    config(['oi-laravel-geo.address_include_city' => true]);

    $address = Address::create([
        'street_1' => '1 Place de la Comédie',
        'city_id' => $city->id,
        'postal_code' => '34000',
    ]);

    $data = $address->toData();

    expect($data->city_id)->toBe($city->id)
        ->and($data->city)->toBeNull()
        ->and($data->full_address)->toContain('Montpellier');
});

it('serializes Data objects to arrays', function () {
    [$country] = createGeoHierarchy();

    expect($country->toData()->toArray())
        ->toHaveKeys(['id', 'code', 'name', 'population', 'surface'])
        ->and($country->toData()->toArray()['code'])->toBe('FR');
});
