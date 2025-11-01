<?php

namespace OiLab\OiLaravelGeo;

use Illuminate\Support\ServiceProvider;
use OiLab\OiLaravelGeo\Commands\InstallOiLaravelGeoCommand;
use OiLab\OiLaravelGeo\Commands\SeedGeoDataCommand;

class OiLaravelGeoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/oi-laravel-geo.php',
            'oi-laravel-geo'
        );

        $this->app->singleton('oi-laravel-geo', function () {
            return new OiLaravelGeoManager;
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/oi-laravel-geo.php' => config_path('oi-laravel-geo.php'),
            ], 'oi-laravel-geo-config');

            // Publish migrations in the correct order to respect foreign key constraints
            $this->publishMigrationsInOrder();

            $this->publishes([
                __DIR__.'/../stubs' => base_path('stubs/oi-laravel-geo'),
            ], 'oi-laravel-geo-stubs');

            $this->publishes([
                __DIR__.'/../resources/geojson' => resource_path('geojson'),
            ], 'oi-laravel-geo-geojson');

            $this->commands([
                InstallOiLaravelGeoCommand::class,
                SeedGeoDataCommand::class,
            ]);
        }

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    /**
     * Publish migrations in the correct order to respect foreign key constraints.
     * Order: countries -> regions -> departments -> cities -> boroughs -> addresses
     * Generates timestamps based on current date with 1 second intervals.
     */
    protected function publishMigrationsInOrder(): void
    {
        $baseTimestamp = now();
        $migrationFiles = [
            '2024_01_01_000001_create_countries_table.php',
            '2024_01_01_000002_create_regions_table.php',
            '2024_01_01_000003_create_departments_table.php',
            '2024_01_01_000004_create_cities_table.php',
            '2024_01_01_000005_create_boroughs_table.php',
            '2024_01_01_000006_create_addresses_table.php',
        ];

        $migrations = [];
        $secondsOffset = 0;

        foreach ($migrationFiles as $migrationFile) {
            $timestamp = $baseTimestamp->copy()->addSeconds($secondsOffset)->format('Y_m_d_His');

            // Extract the migration name (e.g., "create_countries_table")
            preg_match('/_(\d+)_(.+)\.php$/', $migrationFile, $matches);
            $migrationName = $matches[2] ?? '';

            if ($migrationName) {
                $newFileName = "{$timestamp}_{$migrationName}.php";
                $migrations[__DIR__.'/../database/migrations/'.$migrationFile] = database_path("migrations/{$newFileName}");
                $secondsOffset++;
            }
        }

        $this->publishes($migrations, 'oi-laravel-geo-migrations');
    }
}
