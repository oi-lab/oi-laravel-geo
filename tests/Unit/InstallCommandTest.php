<?php

use OiLab\OiLaravelGeo\Services\MigrationGenerator;
use OiLab\OiLaravelGeo\Services\ModelGenerator;

it('can generate migrations with geometry support', function () {
    $configuration = [
        'enable_geometry' => true,
        'geometry_models' => ['cities', 'addresses'],
        'address_include_city' => false,
        'address_include_country' => false,
        'address_include_department' => false,
        'address_include_region' => false,
    ];

    $generator = new MigrationGenerator($configuration);
    $migrations = $generator->generate();

    expect($migrations)
        ->toBeArray()
        ->toHaveCount(6);

    // Check that cities migration includes location and boundary
    $citiesMigration = collect($migrations)->first(fn ($content, $filename) => str_contains($filename, 'create_cities_table'));

    expect($citiesMigration)
        ->toContain('point')
        ->toContain('polygon')
        ->toContain('location')
        ->toContain('boundary');
});

it('can generate migrations without geometry support', function () {
    $configuration = [
        'enable_geometry' => false,
        'address_include_city' => false,
        'address_include_country' => false,
        'address_include_department' => false,
        'address_include_region' => false,
    ];

    $generator = new MigrationGenerator($configuration);
    $migrations = $generator->generate();

    expect($migrations)
        ->toBeArray()
        ->toHaveCount(6);

    // Check that cities migration does NOT include geometry columns
    $citiesMigration = collect($migrations)->first(fn ($content, $filename) => str_contains($filename, 'create_cities_table'));

    expect($citiesMigration)
        ->not->toContain('point')
        ->not->toContain('polygon');
});

it('can generate address migration with city foreign key', function () {
    $configuration = [
        'enable_geometry' => false,
        'address_include_city' => true,
        'address_include_country' => true,
        'address_include_department' => false,
        'address_include_region' => false,
    ];

    $generator = new MigrationGenerator($configuration);
    $migrations = $generator->generate();

    $addressesMigration = collect($migrations)->first(fn ($content, $filename) => str_contains($filename, 'create_addresses_table'));

    expect($addressesMigration)
        ->toContain('city_id')
        ->toContain('country_id')
        ->not->toContain("->string('city')");
});

it('can generate address migration with city string', function () {
    $configuration = [
        'enable_geometry' => false,
        'address_include_city' => false,
        'address_include_country' => false,
        'address_include_department' => false,
        'address_include_region' => false,
    ];

    $generator = new MigrationGenerator($configuration);
    $migrations = $generator->generate();

    $addressesMigration = collect($migrations)->first(fn ($content, $filename) => str_contains($filename, 'create_addresses_table'));

    expect($addressesMigration)
        ->toContain("->string('city')")
        ->not->toContain('city_id');
});

it('can generate models with traits', function () {
    $configuration = [
        'enable_geometry' => true,
        'geometry_models' => ['cities', 'addresses'],
        'models_to_generate' => ['City', 'Address'],
        'address_include_city' => false,
        'address_include_country' => false,
        'address_include_department' => false,
        'address_include_region' => false,
    ];

    $generator = new ModelGenerator($configuration);
    $models = $generator->generate();

    expect($models)
        ->toBeArray()
        ->toHaveCount(2);

    // Check City model includes both HasPoint and HasPolygon traits
    expect($models['City.php'])
        ->toContain('use OiLab\OiLaravelGeo\Traits\HasPoint')
        ->toContain('use OiLab\OiLaravelGeo\Traits\HasPolygon')
        ->toContain('use HasPoint, HasPolygon')
        ->toContain("'location'")
        ->toContain("'boundary'");

    // Check Address model includes only HasPoint trait
    expect($models['Address.php'])
        ->toContain('use OiLab\OiLaravelGeo\Traits\HasPoint')
        ->not->toContain('use OiLab\OiLaravelGeo\Traits\HasPolygon')
        ->toContain('use HasPoint')
        ->toContain("'location'");
});

it('can generate models without traits when geometry is disabled', function () {
    $configuration = [
        'enable_geometry' => false,
        'models_to_generate' => ['City', 'Address'],
        'address_include_city' => false,
        'address_include_country' => false,
        'address_include_department' => false,
        'address_include_region' => false,
    ];

    $generator = new ModelGenerator($configuration);
    $models = $generator->generate();

    expect($models)
        ->toBeArray()
        ->toHaveCount(2);

    // Check City model does NOT include traits
    expect($models['City.php'])
        ->not->toContain('use OiLab\OiLaravelGeo\Traits\HasPoint')
        ->not->toContain('use OiLab\OiLaravelGeo\Traits\HasPolygon');
});

it('includes correct fillable fields for address model with foreign keys', function () {
    $configuration = [
        'enable_geometry' => true,
        'geometry_models' => ['addresses'],
        'models_to_generate' => ['Address'],
        'address_include_city' => true,
        'address_include_country' => true,
        'address_include_department' => true,
        'address_include_region' => true,
    ];

    $generator = new ModelGenerator($configuration);
    $models = $generator->generate();

    expect($models['Address.php'])
        ->toContain("'city_id'")
        ->toContain("'country_id'")
        ->toContain("'department_id'")
        ->toContain("'region_id'")
        ->toContain("'location'")
        ->not->toContain("'city',");
});
