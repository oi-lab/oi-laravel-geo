<?php

namespace OiLab\OiLaravelGeo\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Cast for POLYGON geometry column.
 *
 * Handles both JSON (MySQL/SQLite) and native geometry types (PostgreSQL).
 */
class PolygonCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?array
    {
        if ($value === null) {
            return null;
        }

        // For PostgreSQL with native POLYGON type (binary format)
        if ($this->isPostgreSQL($model)) {
            // PostgreSQL returns POLYGON as WKT string
            if (preg_match('/POLYGON\(\(([^)]+)\)\)/', $value, $matches)) {
                $points = explode(', ', $matches[1]);

                return collect($points)
                    ->map(function ($point) {
                        [$lng, $lat] = explode(' ', $point);

                        return [(float) $lng, (float) $lat];
                    })
                    ->slice(0, -1) // Remove the duplicate closing point
                    ->values()
                    ->toArray();
            }
        }

        // For MySQL/SQLite with JSON
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        // Already an array
        if (is_array($value)) {
            return $value;
        }

        return null;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null) {
            return null;
        }

        if (! is_array($value) || empty($value)) {
            return null;
        }

        // For PostgreSQL with native POLYGON type
        if ($this->isPostgreSQL($model)) {
            $points = collect($value)
                ->map(fn ($point) => "{$point[0]} {$point[1]}")
                ->join(', ');

            // Close the polygon by adding the first point at the end
            $firstPoint = $value[0];
            $points .= ", {$firstPoint[0]} {$firstPoint[1]}";

            return DB::raw("ST_GeomFromText('POLYGON(({$points}))', " . config('oi-laravel-geo.srid', 4326) . ')');
        }

        // For MySQL/SQLite, store as JSON
        // Normalize the array to ensure consistent format
        $normalized = array_map(function ($point) {
            return [
                (float) ($point[0] ?? $point['longitude'] ?? 0),
                (float) ($point[1] ?? $point['latitude'] ?? 0),
            ];
        }, $value);

        return json_encode($normalized);
    }

    /**
     * Check if the model is using PostgreSQL.
     */
    protected function isPostgreSQL(Model $model): bool
    {
        $connection = $model->getConnectionName() ?? config('database.default');
        $driver = config("database.connections.{$connection}.driver");

        return $driver === 'pgsql';
    }
}
