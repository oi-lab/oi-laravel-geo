<?php

namespace OiLab\OiLaravelGeo\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use OiLab\OiLaravelGeo\Services\MigrationGenerator;
use OiLab\OiLaravelGeo\Services\ModelGenerator;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\select;
use function Laravel\Prompts\warning;

class InstallOiLaravelGeoCommand extends Command
{
    protected $signature = 'geo:install
                            {--force : Overwrite existing files}
                            {--config : Publish config only}
                            {--stubs : Publish stubs only}
                            {--geojson : Publish GeoJSON files only}
                            {--interactive : Skip interactive prompts}';

    protected $description = 'Install OiLaravelGeo package resources';

    protected array $configuration = [];

    public function handle(): int
    {
        info('🌍 Welcome to OiLaravelGeo Installation');

        $force = $this->option('force');
        $config = $this->option('config');
        $stubs = $this->option('stubs');
        $geojson = $this->option('geojson');
        $interactive = ! $this->option('interactive');

        $publishAll = ! $config && ! $stubs && ! $geojson;

        // Interactive configuration
        if ($publishAll && $interactive) {
            $this->interactiveConfiguration();
        }

        if ($publishAll || $config) {
            $this->publishConfig($force);
        }

        if ($publishAll) {
            if (! empty($this->configuration)) {
                $this->generateCustomMigrations();
            }
        }

        if ($publishAll || $stubs) {
            if (! empty($this->configuration)) {
                $this->generateCustomModels();
            } else {
                $this->publishStubs($force);
            }
        }

        if ($publishAll || $geojson) {
            $this->publishGeoJson($force);
        }

        // Update configuration file
        if (! empty($this->configuration)) {
            $this->updateConfiguration();
        }

        if ($publishAll && ! empty($this->configuration)) {
            if (confirm('Run migrations now?', true)) {
                $this->call('migrate');
            }
        }

        info('✅ OiLaravelGeo package installed successfully!');
        $this->displayNextSteps();

        return self::SUCCESS;
    }

    protected function interactiveConfiguration(): void
    {
        info('Let\'s configure your geographic package...');

        // Database type
        $this->configuration['database'] = select(
            label: 'Which database are you using?',
            options: [
                'mysql' => 'MySQL 5.7+',
                'pgsql' => 'PostgreSQL with PostGIS',
                'sqlite' => 'SQLite',
            ],
            default: config('database.default', 'mysql')
        );

        // Geometry support
        $this->configuration['enable_geometry'] = confirm(
            label: 'Enable spatial/geometry support (POINT, POLYGON)?',
            default: false,
            hint: 'Required for location-based queries and boundaries'
        );

        if ($this->configuration['enable_geometry']) {
            // Which models need geometry
            $geometryModels = multiselect(
                label: 'Which models should have geometry support?',
                options: [
                    'countries' => 'Countries (boundary polygon)',
                    'regions' => 'Regions (boundary polygon)',
                    'departments' => 'Departments (boundary polygon)',
                    'cities' => 'Cities (location point + optional boundary)',
                    'boroughs' => 'Boroughs (boundary polygon)',
                    'addresses' => 'Addresses (location point)',
                ],
                default: ['cities', 'addresses']
            );

            $this->configuration['geometry_models'] = $geometryModels;
        }

        // Address configuration
        info('Configure Address model...');

        $this->configuration['address_include_city'] = confirm(
            label: 'Use city_id foreign key instead of city string?',
            default: false,
            hint: 'Recommended for relational integrity'
        );

        $this->configuration['address_include_country'] = confirm(
            label: 'Add country_id foreign key to addresses?',
            default: false
        );

        $this->configuration['address_include_department'] = confirm(
            label: 'Add department_id foreign key to addresses?',
            default: false
        );

        $this->configuration['address_include_region'] = confirm(
            label: 'Add region_id foreign key to addresses?',
            default: false
        );

        // Which models to generate
        $this->configuration['models_to_generate'] = multiselect(
            label: 'Which models would you like to generate in app/Models?',
            options: [
                'Country' => 'Country',
                'Region' => 'Region',
                'Department' => 'Department',
                'City' => 'City',
                'Borough' => 'Borough',
                'Address' => 'Address',
            ],
            default: ['Country', 'Region', 'Department', 'City', 'Borough', 'Address']
        );
    }

    protected function publishConfig(bool $force): void
    {
        $params = [
            '--provider' => 'OiLab\OiLaravelGeo\OiLaravelGeoServiceProvider',
            '--tag' => 'oi-laravel-geo-config',
        ];

        if ($force) {
            $params['--force'] = true;
        }

        $this->call('vendor:publish', $params);
    }

    protected function publishStubs(bool $force): void
    {
        $params = [
            '--provider' => 'OiLab\OiLaravelGeo\OiLaravelGeoServiceProvider',
            '--tag' => 'oi-laravel-geo-stubs',
        ];

        if ($force) {
            $params['--force'] = true;
        }

        $this->call('vendor:publish', $params);
    }

    protected function publishGeoJson(bool $force): void
    {
        $params = [
            '--provider' => 'OiLab\OiLaravelGeo\OiLaravelGeoServiceProvider',
            '--tag' => 'oi-laravel-geo-geojson',
        ];

        if ($force) {
            $params['--force'] = true;
        }

        $this->call('vendor:publish', $params);

        warning('GeoJSON files published. You need to provide your own GeoJSON data files.');
    }

    protected function generateCustomMigrations(): void
    {
        info('Generating custom migrations...');

        $generator = new MigrationGenerator($this->configuration);
        $migrationsPath = database_path('migrations');

        $migrations = $generator->generate();

        foreach ($migrations as $filename => $content) {
            $filepath = $migrationsPath.'/'.$filename;
            File::put($filepath, $content);
            $this->components->info("Created migration: {$filename}");
        }
    }

    protected function generateCustomModels(): void
    {
        info('Generating custom models...');

        $generator = new ModelGenerator($this->configuration);
        $modelsPath = app_path('Models');

        if (! File::exists($modelsPath)) {
            File::makeDirectory($modelsPath, 0755, true);
        }

        $models = $generator->generate();

        foreach ($models as $filename => $content) {
            $filepath = $modelsPath.'/'.$filename;
            File::put($filepath, $content);
            $this->components->info("Created model: {$filename}");
        }
    }

    protected function updateConfiguration(): void
    {
        $configPath = config_path('oi-laravel-geo.php');

        if (! File::exists($configPath)) {
            warning('Configuration file not found. Run with --config first.');

            return;
        }

        $config = File::get($configPath);

        // Update enable_geometry
        if (isset($this->configuration['enable_geometry'])) {
            $value = $this->configuration['enable_geometry'] ? 'true' : 'false';
            $config = preg_replace(
                "/'enable_geometry' => (true|false)/",
                "'enable_geometry' => {$value}",
                $config
            );
        }

        // Update address configuration
        if (isset($this->configuration['address_include_city'])) {
            $value = $this->configuration['address_include_city'] ? 'true' : 'false';
            $config = preg_replace(
                "/'address_include_city' => (true|false)/",
                "'address_include_city' => {$value}",
                $config
            );
        }

        if (isset($this->configuration['address_include_country'])) {
            $value = $this->configuration['address_include_country'] ? 'true' : 'false';
            $config = preg_replace(
                "/'address_include_country' => (true|false)/",
                "'address_include_country' => {$value}",
                $config
            );
        }

        if (isset($this->configuration['address_include_department'])) {
            $value = $this->configuration['address_include_department'] ? 'true' : 'false';
            $config = preg_replace(
                "/'address_include_department' => (true|false)/",
                "'address_include_department' => {$value}",
                $config
            );
        }

        if (isset($this->configuration['address_include_region'])) {
            $value = $this->configuration['address_include_region'] ? 'true' : 'false';
            $config = preg_replace(
                "/'address_include_region' => (true|false)/",
                "'address_include_region' => {$value}",
                $config
            );
        }

        // Update model configuration
        if (! empty($this->configuration['models_to_generate'])) {
            $modelsConfig = '';
            foreach ($this->configuration['models_to_generate'] as $model) {
                $modelName = strtolower($model);
                $modelsConfig .= "        '{$modelName}' => \\App\\Models\\{$model}::class,\n";
            }

            $config = preg_replace(
                "/'models' => \[\s*'country'[^\]]+\]/s",
                "'models' => [\n{$modelsConfig}    ]",
                $config
            );
        }

        File::put($configPath, $config);
        $this->components->info('Configuration file updated');
    }

    protected function displayNextSteps(): void
    {
        $this->newLine();
        info('📝 Next steps:');
        $this->line('  1. Review the generated migrations in database/migrations/');
        $this->line('  2. Review the generated models in app/Models/');
        $this->line('  3. Update config/oi-laravel-geo.php if needed');
        $this->line('  4. Place your GeoJSON files in resources/geojson/');
        $this->line('  5. Run: php artisan oi-laravel-geo:seed all');
        $this->newLine();
    }
}
