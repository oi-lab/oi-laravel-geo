<?php

namespace OiLab\OiLaravelGeo\Commands;

use Illuminate\Console\Command;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\warning;

class InstallOiLaravelGeoCommand extends Command
{
    protected $signature = 'geo:install
                            {--force : Overwrite existing files}
                            {--migrations : Publish migrations only}
                            {--config : Publish config only}
                            {--stubs : Publish stubs only}
                            {--geojson : Publish GeoJSON files only}';

    protected $description = 'Install OiLaravelGeo package resources';

    public function handle(): int
    {
        info('Installing OiLaravelGeo package...');

        $force = $this->option('force');
        $migrations = $this->option('migrations');
        $config = $this->option('config');
        $stubs = $this->option('stubs');
        $geojson = $this->option('geojson');

        $publishAll = !$migrations && !$config && !$stubs && !$geojson;

        if ($publishAll || $config) {
            $this->publishConfig($force);
        }

        if ($publishAll || $migrations) {
            $this->publishMigrations($force);
        }

        if ($publishAll || $stubs) {
            $this->publishStubs($force);
        }

        if ($publishAll || $geojson) {
            $this->publishGeoJson($force);
        }

        if ($publishAll || $migrations) {
            if (confirm('Run migrations now?', true)) {
                $this->call('migrate');
            }
        }

        info('OiLaravelGeo package installed successfully!');

        return self::SUCCESS;
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

    protected function publishMigrations(bool $force): void
    {
        $params = [
            '--provider' => 'OiLab\OiLaravelGeo\OiLaravelGeoServiceProvider',
            '--tag' => 'oi-laravel-geo-migrations',
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
}
