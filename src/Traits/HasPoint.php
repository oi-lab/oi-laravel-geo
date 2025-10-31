<?php

namespace OiLab\OiLaravelGeo\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Trait for models with Point geometry support.
 *
 * Requires a 'location' column of type POINT in the database.
 *
 * Usage:
 * - Add 'location' to $fillable array
 * - Add to migration: $table->point('location')->nullable();
 */
trait HasPoint
{
    public function getLatitudeAttribute(): ?float
    {
        if (!$this->location) {
            return null;
        }

        preg_match('/POINT\(([^ ]+) ([^ ]+)\)/', $this->location, $matches);

        return isset($matches[2]) ? (float) $matches[2] : null;
    }

    public function getLongitudeAttribute(): ?float
    {
        if (!$this->location) {
            return null;
        }

        preg_match('/POINT\(([^ ]+) ([^ ]+)\)/', $this->location, $matches);

        return isset($matches[1]) ? (float) $matches[1] : null;
    }

    public function setLocationAttribute(?array $value): void
    {
        if ($value === null) {
            $this->attributes['location'] = null;

            return;
        }

        $longitude = $value['longitude'] ?? $value[0];
        $latitude = $value['latitude'] ?? $value[1];

        $srid = config('oi-laravel-geo.srid', 4326);
        $this->attributes['location'] = "POINT({$longitude} {$latitude})";
    }

    public function scopeNearby(Builder $query, float $latitude, float $longitude, int $radiusInKm = 10): Builder
    {
        $srid = config('oi-laravel-geo.srid', 4326);

        return $query->whereRaw(
            "ST_Distance_Sphere(location, ST_GeomFromText(?, ?)) <= ?",
            ["POINT({$longitude} {$latitude})", $srid, $radiusInKm * 1000]
        );
    }

    public function scopeWithinBounds(Builder $query, float $minLat, float $minLng, float $maxLat, float $maxLng): Builder
    {
        $srid = config('oi-laravel-geo.srid', 4326);
        $polygon = "POLYGON(({$minLng} {$minLat}, {$maxLng} {$minLat}, {$maxLng} {$maxLat}, {$minLng} {$maxLat}, {$minLng} {$minLat}))";

        return $query->whereRaw(
            "ST_Within(location, ST_GeomFromText(?, ?))",
            [$polygon, $srid]
        );
    }
}
