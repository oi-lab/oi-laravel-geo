<?php

namespace OiLab\OiLaravelGeo\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Cast for POINT geometry column.
 *
 * Handles both JSON (MySQL/SQLite) and native geometry types (PostgreSQL).
 */
class PointCast implements CastsAttributes
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

        // For PostgreSQL with native POINT type (binary format)
        if ($this->isPostgreSQL($model)) {
            // PostgreSQL returns POINT as WKT string
            if (preg_match('/POINT\(([^ ]+) ([^ ]+)\)/', $value, $matches)) {
                return [
                    'longitude' => (float) $matches[1],
                    'latitude' => (float) $matches[2],
                ];
            }
        }

        // For MySQL/SQLite with JSON
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                if (isset($decoded['longitude'], $decoded['latitude'])) {
                    return [
                        'longitude' => (float) $decoded['longitude'],
                        'latitude' => (float) $decoded['latitude'],
                    ];
                }

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

        // Extract longitude and latitude from array
        $longitude = $value['longitude'] ?? $value[0] ?? null;
        $latitude = $value['latitude'] ?? $value[1] ?? null;

        if ($longitude === null || $latitude === null) {
            return null;
        }

        // For PostgreSQL with native POINT type
        if ($this->isPostgreSQL($model)) {
            // Return WKT format for PostgreSQL
            return DB::raw("ST_GeomFromText('POINT({$longitude} {$latitude})', ".config('oi-laravel-geo.srid', 4326).')');
        }

        // For MySQL/SQLite, store as JSON
        return json_encode([
            'longitude' => (float) $longitude,
            'latitude' => (float) $latitude,
        ]);
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
