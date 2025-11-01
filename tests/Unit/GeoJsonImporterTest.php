<?php

use OiLab\OiLaravelGeo\Models\Borough;
use OiLab\OiLaravelGeo\Models\City;
use OiLab\OiLaravelGeo\Models\Country;
use OiLab\OiLaravelGeo\Models\Department;
use OiLab\OiLaravelGeo\Models\Region;
use OiLab\OiLaravelGeo\Services\BoroughImporter;
use OiLab\OiLaravelGeo\Services\CityImporter;
use OiLab\OiLaravelGeo\Services\CountryImporter;
use OiLab\OiLaravelGeo\Services\DepartmentImporter;
use OiLab\OiLaravelGeo\Services\RegionImporter;

beforeEach(function () {
    $this->countryImporter = new CountryImporter;
    $this->regionImporter = new RegionImporter;
    $this->departmentImporter = new DepartmentImporter;
    $this->cityImporter = new CityImporter;
    $this->boroughImporter = new BoroughImporter;
});

it('can import countries from geojson', function () {
    $geojson = [
        'type' => 'FeatureCollection',
        'features' => [
            [
                'type' => 'Feature',
                'properties' => [
                    'code' => 'FR',
                    'name' => 'France',
                    'population' => 67000000,
                    'surface' => 551695,
                ],
                'geometry' => [],
            ],
        ],
    ];

    $tempFile = tempnam(sys_get_temp_dir(), 'geojson');
    file_put_contents($tempFile, json_encode($geojson));

    $count = $this->countryImporter->import($tempFile);

    expect($count)->toBe(1)
        ->and(Country::where('code', 'FR')->first())
        ->name->toBe('France')
        ->population->toBe(67000000)
        ->surface->toBe(551695);

    unlink($tempFile);
});

it('throws exception for missing geojson file', function () {
    expect(fn () => $this->countryImporter->import('/nonexistent/file.geojson'))
        ->toThrow(\RuntimeException::class, 'GeoJSON file not found');
});

it('throws exception for invalid geojson format', function () {
    $tempFile = tempnam(sys_get_temp_dir(), 'geojson');
    file_put_contents($tempFile, json_encode(['invalid' => 'format']));

    expect(fn () => $this->countryImporter->import($tempFile))
        ->toThrow(\RuntimeException::class, 'Invalid GeoJSON format');

    unlink($tempFile);
});

it('can import regions from geojson', function () {
    $country = Country::create(['code' => 'FR', 'name' => 'France']);

    $geojson = [
        'type' => 'FeatureCollection',
        'features' => [
            [
                'type' => 'Feature',
                'properties' => [
                    'country_code' => 'FR',
                    'code' => 'IDF',
                    'name' => 'Île-de-France',
                    'population' => 12000000,
                    'surface' => 12012,
                ],
                'geometry' => [],
            ],
        ],
    ];

    $tempFile = tempnam(sys_get_temp_dir(), 'geojson');
    file_put_contents($tempFile, json_encode($geojson));

    $count = $this->regionImporter->import($tempFile);

    expect($count)->toBe(1)
        ->and(Region::where('code', 'IDF')->first())
        ->name->toBe('Île-de-France')
        ->country_id->toBe($country->id);

    unlink($tempFile);
});

it('can import departments from geojson', function () {
    $country = Country::create(['code' => 'FR', 'name' => 'France']);
    $region = Region::create([
        'country_id' => $country->id,
        'code' => 'IDF',
        'name' => 'Île-de-France',
    ]);

    $geojson = [
        'type' => 'FeatureCollection',
        'features' => [
            [
                'type' => 'Feature',
                'properties' => [
                    'region_code' => 'IDF',
                    'code' => '75',
                    'name' => 'Paris',
                    'population' => 2200000,
                    'surface' => 105,
                ],
                'geometry' => [],
            ],
        ],
    ];

    $tempFile = tempnam(sys_get_temp_dir(), 'geojson');
    file_put_contents($tempFile, json_encode($geojson));

    $count = $this->departmentImporter->import($tempFile);

    expect($count)->toBe(1)
        ->and(Department::where('code', '75')->first())
        ->name->toBe('Paris')
        ->region_id->toBe($region->id);

    unlink($tempFile);
});

it('can import cities from geojson', function () {
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

    $geojson = [
        'type' => 'FeatureCollection',
        'features' => [
            [
                'type' => 'Feature',
                'properties' => [
                    'department_code' => '75',
                    'identifier' => '75056',
                    'code' => '75056',
                    'name' => 'Paris',
                    'population' => 2200000,
                    'surface' => 105,
                ],
                'geometry' => [],
            ],
        ],
    ];

    $tempFile = tempnam(sys_get_temp_dir(), 'geojson');
    file_put_contents($tempFile, json_encode($geojson));

    $count = $this->cityImporter->import($tempFile);

    expect($count)->toBe(1)
        ->and(City::where('identifier', '75056')->first())
        ->name->toBe('Paris')
        ->department_id->toBe($department->id);

    unlink($tempFile);
});

it('updates existing records on re-import', function () {
    $country = Country::create(['code' => 'FR', 'name' => 'France']);

    $geojson = [
        'type' => 'FeatureCollection',
        'features' => [
            [
                'type' => 'Feature',
                'properties' => [
                    'code' => 'FR',
                    'name' => 'France Updated',
                    'population' => 68000000,
                ],
                'geometry' => [],
            ],
        ],
    ];

    $tempFile = tempnam(sys_get_temp_dir(), 'geojson');
    file_put_contents($tempFile, json_encode($geojson));

    $this->countryImporter->import($tempFile);

    $updated = Country::where('code', 'FR')->first();

    expect($updated)
        ->name->toBe('France Updated')
        ->population->toBe(68000000);

    expect(Country::count())->toBe(1);

    unlink($tempFile);
});

it('can import boroughs from geojson', function () {
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

    $geojson = [
        'type' => 'FeatureCollection',
        'features' => [
            [
                'type' => 'Feature',
                'properties' => [
                    'city_identifier' => '75056',
                    'code' => '01',
                    'name' => '1er Arrondissement',
                    'population' => 16000,
                    'surface' => 183,
                ],
                'geometry' => [],
            ],
        ],
    ];

    $tempFile = tempnam(sys_get_temp_dir(), 'geojson');
    file_put_contents($tempFile, json_encode($geojson));

    $count = $this->boroughImporter->import($tempFile);

    expect($count)->toBe(1)
        ->and(Borough::where('code', '01')->first())
        ->name->toBe('1er Arrondissement')
        ->city_id->toBe($city->id);

    unlink($tempFile);
});
