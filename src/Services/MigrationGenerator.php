<?php

namespace OiLab\OiLaravelGeo\Services;

class MigrationGenerator
{
    public function __construct(protected array $configuration) {}

    public function generate(): array
    {
        $migrations = [];
        $baseTimestamp = now();
        $tables = ['countries', 'regions', 'departments', 'cities', 'boroughs', 'addresses'];
        $modelsToGenerate = $this->configuration['models_to_generate'] ?? [];

        // Map model names to table names
        $modelToTable = [
            'Country' => 'countries',
            'Region' => 'regions',
            'Department' => 'departments',
            'City' => 'cities',
            'Borough' => 'boroughs',
            'Address' => 'addresses',
        ];

        // Define dependencies (child => [parents])
        $dependencies = [
            'regions' => ['countries'],
            'departments' => ['countries', 'regions'],
            'cities' => ['countries', 'regions', 'departments'],
            'boroughs' => ['countries', 'regions', 'departments', 'cities'],
            'addresses' => $this->getAddressDependencies(),
        ];

        // Filter tables based on models_to_generate
        $tablesToGenerate = [];
        foreach ($modelsToGenerate as $model) {
            if (isset($modelToTable[$model])) {
                $tablesToGenerate[] = $modelToTable[$model];
            }
        }

        // If no specific models are requested, generate all tables
        if (empty($tablesToGenerate)) {
            $tablesToGenerate = $tables;
        }

        // Add required dependencies
        $tablesToGenerate = $this->addRequiredDependencies($tablesToGenerate, $dependencies);

        // Maintain dependency order
        $orderedTables = array_intersect($tables, $tablesToGenerate);

        $secondsOffset = 0;
        foreach ($orderedTables as $table) {
            $timestamp = $baseTimestamp->copy()->addSeconds($secondsOffset)->format('Y_m_d_His');
            $methodName = 'generate'.ucfirst($table).'Migration';

            $migrations["{$timestamp}_create_{$table}_table.php"] = $this->$methodName();
            $secondsOffset++;
        }

        return $migrations;
    }

    /**
     * Get address table dependencies based on configuration.
     */
    protected function getAddressDependencies(): array
    {
        $dependencies = [];

        if ($this->configuration['address_include_city'] ?? false) {
            $dependencies = array_merge($dependencies, ['countries', 'regions', 'departments', 'cities']);
        }

        if ($this->configuration['address_include_country'] ?? false) {
            $dependencies[] = 'countries';
        }

        if ($this->configuration['address_include_region'] ?? false) {
            $dependencies = array_merge($dependencies, ['countries', 'regions']);
        }

        if ($this->configuration['address_include_department'] ?? false) {
            $dependencies = array_merge($dependencies, ['countries', 'regions', 'departments']);
        }

        return array_unique($dependencies);
    }

    /**
     * Add required parent tables based on dependencies.
     */
    protected function addRequiredDependencies(array $tables, array $dependencies): array
    {
        $result = $tables;

        foreach ($tables as $table) {
            if (isset($dependencies[$table])) {
                foreach ($dependencies[$table] as $dependency) {
                    if (! in_array($dependency, $result)) {
                        $result[] = $dependency;
                    }
                }
            }
        }

        return array_unique($result);
    }

    protected function generateCountriesMigration(): string
    {
        $hasGeometry = $this->shouldHaveGeometry('countries');
        $geometryColumns = $hasGeometry ? $this->getPolygonColumn('boundary') : '';

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
        $geometryColumns = $hasGeometry ? $this->getPolygonColumn('boundary') : '';

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
        $geometryColumns = $hasGeometry ? $this->getPolygonColumn('boundary') : '';

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
            $geometryColumns = $this->getPointColumn('location').$this->getPolygonColumn('boundary');
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
        $geometryColumns = $hasGeometry ? $this->getPolygonColumn('boundary') : '';

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
        $geometryColumns = $hasGeometry ? $this->getPointColumn('location') : '';

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
            {$cityField}
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

    /**
     * Get the appropriate point column definition based on database type.
     */
    protected function getPointColumn(string $columnName): string
    {
        $database = $this->configuration['database'] ?? 'mysql';

        return match ($database) {
            'pgsql' => "\n            \$table->point('{$columnName}')->nullable();",
            'mysql', 'sqlite' => "\n            \$table->json('{$columnName}')->nullable();",
            default => "\n            \$table->json('{$columnName}')->nullable();",
        };
    }

    /**
     * Get the appropriate polygon column definition based on database type.
     */
    protected function getPolygonColumn(string $columnName): string
    {
        $database = $this->configuration['database'] ?? 'mysql';

        return match ($database) {
            'pgsql' => "\n            \$table->polygon('{$columnName}')->nullable();",
            'mysql', 'sqlite' => "\n            \$table->json('{$columnName}')->nullable();",
            default => "\n            \$table->json('{$columnName}')->nullable();",
        };
    }
}
