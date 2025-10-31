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
            return new OiLaravelGeoManager();
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/oi-laravel-geo.php' => config_path('oi-laravel-geo.php'),
            ], 'oi-laravel-geo-config');

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'oi-laravel-geo-migrations');

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
}
