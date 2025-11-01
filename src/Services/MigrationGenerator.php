<?php

namespace OiLab\OiLaravelGeo\Services;

class MigrationGenerator
{
    public function __construct(protected array $configuration) {}

    public function generate(): array
    {
        $migrations = [];
        $timestamp = now()->format('Y_m_d_His');

        $migrations["{$timestamp}_create_countries_table.php"] = $this->generateCountriesMigration();
        $migrations["{$timestamp}_create_regions_table.php"] = $this->generateRegionsMigration();
        $migrations["{$timestamp}_create_departments_table.php"] = $this->generateDepartmentsMigration();
        $migrations["{$timestamp}_create_cities_table.php"] = $this->generateCitiesMigration();
        $migrations["{$timestamp}_create_boroughs_table.php"] = $this->generateBoroughsMigration();
        $migrations["{$timestamp}_create_addresses_table.php"] = $this->generateAddressesMigration();

        return $migrations;
    }

    protected function generateCountriesMigration(): string
    {
        $hasGeometry = $this->shouldHaveGeometry('countries');
        $geometryColumns = $hasGeometry ? "\n            \$table->polygon('boundary')->nullable();" : '';

        return <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('oi-laravel-geo.tables.countries', 'countries'), function (Blueprint \$table) {
            \$table->id();
            \$table->string('code')->unique();
            \$table->string('name');
            \$table->integer('population')->nullable();
            \$table->integer('surface')->nullable();{$geometryColumns}
            \$table->timestamps();

            \$table->index('code');
            \$table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('oi-laravel-geo.tables.countries', 'countries'));
    }
};

PHP;
    }

    protected function generateRegionsMigration(): string
    {
        $hasGeometry = $this->shouldHaveGeometry('regions');
        $geometryColumns = $hasGeometry ? "\n            \$table->polygon('boundary')->nullable();" : '';

        return <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('oi-laravel-geo.tables.regions', 'regions'), function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('country_id')->constrained(config('oi-laravel-geo.tables.countries', 'countries'))->cascadeOnDelete();
            \$table->string('code');
            \$table->string('name');
            \$table->integer('population')->nullable();
            \$table->integer('surface')->nullable();{$geometryColumns}
            \$table->timestamps();

            \$table->unique(['country_id', 'code']);
            \$table->index('code');
            \$table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('oi-laravel-geo.tables.regions', 'regions'));
    }
};

PHP;
    }

    protected function generateDepartmentsMigration(): string
    {
        $hasGeometry = $this->shouldHaveGeometry('departments');
        $geometryColumns = $hasGeometry ? "\n            \$table->polygon('boundary')->nullable();" : '';

        return <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('oi-laravel-geo.tables.departments', 'departments'), function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('region_id')->constrained(config('oi-laravel-geo.tables.regions', 'regions'))->cascadeOnDelete();
            \$table->string('code');
            \$table->string('name');
            \$table->integer('population')->nullable();
            \$table->integer('surface')->nullable();{$geometryColumns}
            \$table->timestamps();

            \$table->unique(['region_id', 'code']);
            \$table->index('code');
            \$table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('oi-laravel-geo.tables.departments', 'departments'));
    }
};

PHP;
    }

    protected function generateCitiesMigration(): string
    {
        $hasGeometry = $this->shouldHaveGeometry('cities');
        $geometryColumns = '';

        if ($hasGeometry) {
            $geometryColumns = "\n            \$table->point('location')->nullable();\n            \$table->polygon('boundary')->nullable();";
        }

        return <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('oi-laravel-geo.tables.cities', 'cities'), function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('department_id')->constrained(config('oi-laravel-geo.tables.departments', 'departments'))->cascadeOnDelete();
            \$table->string('identifier')->unique();
            \$table->string('code');
            \$table->string('name');
            \$table->integer('population')->nullable();
            \$table->integer('surface')->nullable();{$geometryColumns}
            \$table->timestamps();

            \$table->unique(['department_id', 'code']);
            \$table->index('code');
            \$table->index('name');
            \$table->index('identifier');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('oi-laravel-geo.tables.cities', 'cities'));
    }
};

PHP;
    }

    protected function generateBoroughsMigration(): string
    {
        $hasGeometry = $this->shouldHaveGeometry('boroughs');
        $geometryColumns = $hasGeometry ? "\n            \$table->polygon('boundary')->nullable();" : '';

        return <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('oi-laravel-geo.tables.boroughs', 'boroughs'), function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('city_id')->constrained(config('oi-laravel-geo.tables.cities', 'cities'))->cascadeOnDelete();
            \$table->string('code');
            \$table->string('name');
            \$table->integer('population')->nullable();
            \$table->integer('surface')->nullable();{$geometryColumns}
            \$table->timestamps();

            \$table->unique(['city_id', 'code']);
            \$table->index('code');
            \$table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('oi-laravel-geo.tables.boroughs', 'boroughs'));
    }
};

PHP;
    }

    protected function generateAddressesMigration(): string
    {
        $hasGeometry = $this->shouldHaveGeometry('addresses');
        $geometryColumns = $hasGeometry ? "\n            \$table->point('location')->nullable();" : '';

        // City field
        $cityField = $this->configuration['address_include_city'] ?? false
            ? "\$table->foreignId('city_id')->nullable()->constrained(config('oi-laravel-geo.tables.cities', 'cities'))->nullOnDelete();"
            : "\$table->string('city');";

        // Optional foreign keys
        $optionalFields = '';
        if ($this->configuration['address_include_country'] ?? false) {
            $optionalFields .= "\n            \$table->foreignId('country_id')->nullable()->constrained(config('oi-laravel-geo.tables.countries', 'countries'))->nullOnDelete();";
        }
        if ($this->configuration['address_include_department'] ?? false) {
            $optionalFields .= "\n            \$table->foreignId('department_id')->nullable()->constrained(config('oi-laravel-geo.tables.departments', 'departments'))->nullOnDelete();";
        }
        if ($this->configuration['address_include_region'] ?? false) {
            $optionalFields .= "\n            \$table->foreignId('region_id')->nullable()->constrained(config('oi-laravel-geo.tables.regions', 'regions'))->nullOnDelete();";
        }

        return <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('oi-laravel-geo.tables.addresses', 'addresses'), function (Blueprint \$table) {
            \$table->id();
            \$table->string('name')->nullable();
            \$table->string('street_1');
            \$table->string('street_2')->nullable();
            \$table->string('street_3')->nullable();
            \$table->{$cityField}
            \$table->string('postal_code');{$optionalFields}{$geometryColumns}
            \$table->timestamps();

            \$table->index('postal_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('oi-laravel-geo.tables.addresses', 'addresses'));
    }
};

PHP;
    }

    protected function shouldHaveGeometry(string $table): bool
    {
        if (! ($this->configuration['enable_geometry'] ?? false)) {
            return false;
        }

        return in_array($table, $this->configuration['geometry_models'] ?? []);
    }
}
