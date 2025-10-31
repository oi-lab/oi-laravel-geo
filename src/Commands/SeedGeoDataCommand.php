<?php

namespace OiLab\OiLaravelGeo\Commands;

use Illuminate\Console\Command;
use OiLab\OiLaravelGeo\Services\BoroughImporter;
use OiLab\OiLaravelGeo\Services\CityImporter;
use OiLab\OiLaravelGeo\Services\CountryImporter;
use OiLab\OiLaravelGeo\Services\DepartmentImporter;
use OiLab\OiLaravelGeo\Services\RegionImporter;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\select;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\warning;

class SeedGeoDataCommand extends Command
{
    protected $signature = 'geo:seed
                            {type? : Type of data to seed (countries, regions, departments, cities, boroughs, all)}
                            {--file= : Path to GeoJSON file}';

    protected $description = 'Seed geographic data from GeoJSON files';

    public function __construct(
        protected CountryImporter $countryImporter,
        protected RegionImporter $regionImporter,
        protected DepartmentImporter $departmentImporter,
        protected CityImporter $cityImporter,
        protected BoroughImporter $boroughImporter
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $type = $this->argument('type');

        if (!$type) {
            $type = select(
                'What type of data do you want to seed?',
                ['countries', 'regions', 'departments', 'cities', 'boroughs', 'all']
            );
        }

        if ($type === 'all') {
            return $this->seedAll();
        }

        return $this->seedType($type);
    }

    protected function seedAll(): int
    {
        $types = ['countries', 'regions', 'departments', 'cities', 'boroughs'];

        foreach ($types as $type) {
            $result = $this->seedType($type);

            if ($result !== self::SUCCESS) {
                return $result;
            }
        }

        info('All geographic data seeded successfully!');

        return self::SUCCESS;
    }

    protected function seedType(string $type): int
    {
        $filePath = $this->option('file') ?? $this->getDefaultFilePath($type);

        if (!file_exists($filePath)) {
            error("GeoJSON file not found: {$filePath}");
            warning("Please provide a valid GeoJSON file using --file option or place it at: {$filePath}");

            return self::FAILURE;
        }

        try {
            $count = spin(
                fn () => match ($type) {
                    'countries' => $this->countryImporter->import($filePath),
                    'regions' => $this->regionImporter->import($filePath),
                    'departments' => $this->departmentImporter->import($filePath),
                    'cities' => $this->cityImporter->import($filePath),
                    'boroughs' => $this->boroughImporter->import($filePath),
                    default => throw new \InvalidArgumentException("Invalid type: {$type}")
                },
                "Importing {$type}..."
            );

            info("Successfully imported {$count} {$type}");

            return self::SUCCESS;
        } catch (\Exception $e) {
            error("Error importing {$type}: ".$e->getMessage());

            return self::FAILURE;
        }
    }

    protected function getDefaultFilePath(string $type): string
    {
        $geoJsonPath = config('oi-laravel-geo.geojson_path', resource_path('geojson'));

        return "{$geoJsonPath}/{$type}.geojson";
    }
}
