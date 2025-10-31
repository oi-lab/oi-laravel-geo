<?php

use OiLab\OiLaravelGeo\Models\Address;
use OiLab\OiLaravelGeo\Models\City;
use OiLab\OiLaravelGeo\Models\Country;
use OiLab\OiLaravelGeo\Models\Department;
use OiLab\OiLaravelGeo\Models\Region;

it('can create an address with string city (default config)', function () {
    $address = Address::create([
        'name' => 'Eiffel Tower',
        'street_1' => '5 Avenue Anatole France',
        'city' => 'Paris',
        'postal_code' => '75007',
    ]);

    expect($address)
        ->name->toBe('Eiffel Tower')
        ->street_1->toBe('5 Avenue Anatole France')
        ->street_2->toBeNull()
        ->street_3->toBeNull()
        ->city->toBe('Paris')
        ->postal_code->toBe('75007');
});

it('can create an address with city_id when enabled', function () {
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

    config(['oi-laravel-geo.address_include_city' => true]);
    config(['oi-laravel-geo.address_include_country' => true]);

    $address = Address::create([
        'name' => 'Eiffel Tower',
        'street_1' => '5 Avenue Anatole France',
        'city_id' => $city->id,
        'postal_code' => '75007',
        'country_id' => $country->id,
    ]);

    expect($address)
        ->name->toBe('Eiffel Tower')
        ->street_1->toBe('5 Avenue Anatole France')
        ->city_id->toBe($city->id)
        ->postal_code->toBe('75007')
        ->country_id->toBe($country->id);
})->skip('Migration structure depends on config at migration time');

it('city_name attribute returns city string when city_id disabled', function () {
    $address = Address::create([
        'name' => 'Eiffel Tower',
        'street_1' => '5 Avenue Anatole France',
        'city' => 'Paris',
        'postal_code' => '75007',
    ]);

    expect($address->city_name)->toBe('Paris');
});

it('can have optional street_2 and street_3', function () {
    $address = Address::create([
        'name' => 'Company Name',
        'street_1' => '123 Main Street',
        'street_2' => 'Building B',
        'street_3' => 'Floor 5',
        'city' => 'Paris',
        'postal_code' => '75001',
    ]);

    expect($address)
        ->street_2->toBe('Building B')
        ->street_3->toBe('Floor 5');
});

it('generates full address attribute with string city', function () {
    $address = Address::create([
        'name' => 'Eiffel Tower',
        'street_1' => '5 Avenue Anatole France',
        'city' => 'Paris',
        'postal_code' => '75007',
    ]);

    expect($address->full_address)->toBe('Eiffel Tower, 5 Avenue Anatole France, 75007, Paris');
});
