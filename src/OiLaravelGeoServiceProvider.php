<?php

namespace OiLab\OiLaravelGeo;

use Illuminate\Support\ServiceProvider;
use OiLab\OiLaravelGeo\Commands\InstallOiLaravelGeoCommand;
use OiLab\OiLaravelGeo\Commands\SeedGeoDataCommand;
use OiLab\OiLaravelGeo\Models\Address;
use OiLab\OiLaravelGeo\Observers\AddressObserver;

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
        $this->registerAddressObserver();

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/oi-laravel-geo.php' => config_path('oi-laravel-geo.php'),
            ], 'oi-laravel-geo-config');

            $this->publishes([
                __DIR__.'/../stubs' => base_path('stubs/oi-laravel-geo'),
            ], 'oi-laravel-geo-stubs');

            $this->publishes([
                __DIR__.'/../resources/geojson' => resource_path('geojson'),
            ], 'oi-laravel-geo-geojson');

            $this->publishes([
                __DIR__.'/../resources/stubs/ai-skill.md' => base_path('.claude/skills/oilab-laravel-geo/SKILL.md'),
            ], 'oi-laravel-geo-skill');

            $this->commands([
                InstallOiLaravelGeoCommand::class,
                SeedGeoDataCommand::class,
            ]);
        }
    }

    /**
     * The observer enforces one default address per holder. It is registered
     * unconditionally and no-ops when `address_morphable` is disabled, so the
     * setting stays switchable at runtime.
     */
    protected function registerAddressObserver(): void
    {
        /** @var class-string<Address> $addressModel */
        $addressModel = config('oi-laravel-geo.models.address') ?? Address::class;

        $addressModel::observe(AddressObserver::class);
    }
}
