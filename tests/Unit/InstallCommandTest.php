<?php

use OiLab\OiLaravelGeo\Services\MigrationGenerator;
use OiLab\OiLaravelGeo\Services\ModelGenerator;

it('can generate migrations with geometry support', function () {
    $configuration = [
        'database' => 'pgsql',
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

it('generates the exact v1.1.1 addresses migration with the default configuration', function () {
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

    expect($addressesMigration)->toBe(
        file_get_contents(__DIR__.'/../Fixtures/expected/v1_1_1_addresses_migration.php.txt')
    );
});

it('generates the exact v1.1.1 addresses migration when the new keys are explicitly off', function () {
    $configuration = [
        'enable_geometry' => false,
        'address_include_city' => false,
        'address_include_country' => false,
        'address_include_department' => false,
        'address_include_region' => false,
        'address_morphable' => false,
        'address_key_type' => 'id',
        'address_geocoding' => false,
    ];

    $generator = new MigrationGenerator($configuration);
    $migrations = $generator->generate();

    $addressesMigration = collect($migrations)->first(fn ($content, $filename) => str_contains($filename, 'create_addresses_table'));

    expect($addressesMigration)->toBe(
        file_get_contents(__DIR__.'/../Fixtures/expected/v1_1_1_addresses_migration.php.txt')
    );
});

it('adds morph columns and a composite index when addresses are morphable', function () {
    $configuration = [
        'enable_geometry' => false,
        'address_include_city' => false,
        'address_include_country' => false,
        'address_include_department' => false,
        'address_include_region' => false,
        'address_morphable' => true,
    ];

    $generator = new MigrationGenerator($configuration);
    $migrations = $generator->generate();

    $addressesMigration = collect($migrations)->first(fn ($content, $filename) => str_contains($filename, 'create_addresses_table'));

    expect($addressesMigration)
        ->toContain("\$table->string('addressable_type')->nullable();")
        ->toContain("\$table->string('addressable_id', 36)->nullable();")
        ->toContain("\$table->boolean('is_default')->default(false);")
        ->toContain("\$table->index(['addressable_type', 'addressable_id']);")
        ->not->toContain('morphs(');
});

it('emits a ulid primary key when address_key_type is ulid', function () {
    $configuration = [
        'enable_geometry' => false,
        'address_include_city' => false,
        'address_include_country' => false,
        'address_include_department' => false,
        'address_include_region' => false,
        'address_key_type' => 'ulid',
    ];

    $generator = new MigrationGenerator($configuration);
    $migrations = $generator->generate();

    $addressesMigration = collect($migrations)->first(fn ($content, $filename) => str_contains($filename, 'create_addresses_table'));

    expect($addressesMigration)
        ->toContain("\$table->ulid('id')->primary();")
        ->not->toContain('$table->id();');
});

it('adds the geocoding columns when address_geocoding is enabled', function () {
    $configuration = [
        'enable_geometry' => false,
        'address_include_city' => false,
        'address_include_country' => false,
        'address_include_department' => false,
        'address_include_region' => false,
        'address_geocoding' => true,
    ];

    $generator = new MigrationGenerator($configuration);
    $migrations = $generator->generate();

    $addressesMigration = collect($migrations)->first(fn ($content, $filename) => str_contains($filename, 'create_addresses_table'));

    expect($addressesMigration)
        ->toContain("\$table->decimal('latitude', 10, 7)->nullable();")
        ->toContain("\$table->decimal('longitude', 10, 7)->nullable();")
        ->toContain("\$table->string('geocoded_label', 255)->nullable();")
        ->toContain("\$table->decimal('geocoding_score', 4, 3)->nullable();")
        ->toContain("\$table->string('ban_id', 32)->nullable();")
        ->toContain("\$table->timestamp('geocoded_at')->nullable();")
        ->toContain("\$table->index(['latitude', 'longitude']);");
});

it('keeps geocoding columns and the geometry point side by side', function () {
    $configuration = [
        'enable_geometry' => true,
        'geometry_models' => ['addresses'],
        'database' => 'pgsql',
        'address_include_city' => false,
        'address_include_country' => false,
        'address_include_department' => false,
        'address_include_region' => false,
        'address_geocoding' => true,
    ];

    $generator = new MigrationGenerator($configuration);
    $migrations = $generator->generate();

    $addressesMigration = collect($migrations)->first(fn ($content, $filename) => str_contains($filename, 'create_addresses_table'));

    expect($addressesMigration)
        ->toContain("\$table->point('location')->nullable();")
        ->toContain("\$table->decimal('latitude', 10, 7)->nullable();");
});

it('adds the morph and geocoding fields to the generated address model', function () {
    $configuration = [
        'enable_geometry' => false,
        'models_to_generate' => ['Address'],
        'address_include_city' => false,
        'address_include_country' => false,
        'address_include_department' => false,
        'address_include_region' => false,
        'address_morphable' => true,
        'address_geocoding' => true,
    ];

    $generator = new ModelGenerator($configuration);
    $models = $generator->generate();

    expect($models['Address.php'])
        ->toContain("'addressable_type'")
        ->toContain("'addressable_id'")
        ->toContain("'is_default'")
        ->toContain("'latitude'")
        ->toContain("'longitude'")
        ->toContain("'geocoded_label'")
        ->toContain("'geocoding_score'")
        ->toContain("'ban_id'")
        ->toContain("'geocoded_at'");
});
