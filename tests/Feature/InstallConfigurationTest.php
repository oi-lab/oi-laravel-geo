<?php

use Illuminate\Console\OutputStyle;
use Illuminate\Console\View\Components\Factory;
use OiLab\OiLaravelGeo\Commands\InstallOiLaravelGeoCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Runs the installer's config rewriting step against a copy of the shipped config.
 *
 * @param  array<string, mixed>  $configuration
 */
function writeInstallerConfiguration(array $configuration, string $configDirectory): string
{
    copy(__DIR__.'/../../config/oi-laravel-geo.php', $configDirectory.'/oi-laravel-geo.php');

    app()->useConfigPath($configDirectory);

    $command = new InstallOiLaravelGeoCommand;
    $reflection = new ReflectionClass($command);

    $reflection->getProperty('configuration')->setValue($command, $configuration);
    $reflection->getProperty('components')->setValue($command, new Factory(
        new OutputStyle(new ArrayInput([]), new BufferedOutput)
    ));

    $method = $reflection->getMethod('updateConfiguration');
    $method->setAccessible(true);
    $method->invoke($command);

    return file_get_contents($configDirectory.'/oi-laravel-geo.php');
}

beforeEach(function () {
    $this->configDirectory = sys_get_temp_dir().'/oi-laravel-geo-'.uniqid();
    mkdir($this->configDirectory);
});

afterEach(function () {
    array_map('unlink', glob($this->configDirectory.'/*'));
    rmdir($this->configDirectory);
});

it('writes the new address options into the published config', function () {
    $config = writeInstallerConfiguration([
        'address_morphable' => true,
        'address_key_type' => 'ulid',
        'address_geocoding' => true,
    ], $this->configDirectory);

    expect($config)
        ->toContain("'address_morphable' => true,")
        ->toContain("'address_key_type' => 'ulid',")
        ->toContain("'address_geocoding' => true,");
});

it('keeps the defaults when the new options are declined', function () {
    $config = writeInstallerConfiguration([
        'address_morphable' => false,
        'address_key_type' => 'id',
        'address_geocoding' => false,
    ], $this->configDirectory);

    expect($config)
        ->toContain("'address_morphable' => false,")
        ->toContain("'address_key_type' => 'id',")
        ->toContain("'address_geocoding' => false,");
});

it('leaves the new options untouched when the installer never asked', function () {
    $config = writeInstallerConfiguration([
        'enable_geometry' => true,
    ], $this->configDirectory);

    expect($config)
        ->toContain("'enable_geometry' => true,")
        ->toContain("'address_morphable' => false,")
        ->toContain("'address_key_type' => 'id',")
        ->toContain("'address_geocoding' => false,");
});
