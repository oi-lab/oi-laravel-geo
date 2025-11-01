<?php

namespace OiLab\OiLaravelGeo\Traits;

use Illuminate\Database\Eloquent\Builder;
use OiLab\OiLaravelGeo\Services\GeoQueryBuilder;

/**
 * Trait for models with Point geometry support.
 *
 * Requires a 'location' column of type POINT in the database.
 *
 * Usage:
 * - Add 'location' to $fillable array
 * - Add to migration: $table->point('location')->nullable();
 *
 * Supports MySQL, PostgreSQL (PostGIS), and SQLite.
 */
trait HasPoint
{
    public function getLatitudeAttribute(): ?float
    {
        if (! $this->location) {
            return null;
        }

        preg_match('/POINT\(([^ ]+) ([^ ]+)\)/', $this->location, $matches);

        return isset($matches[2]) ? (float) $matches[2] : null;
    }

    public function getLongitudeAttribute(): ?float
    {
        if (! $this->location) {
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

    /**
     * Scope to find points within a radius from a central point.
     */
    public function scopeNearby(Builder $query, float $latitude, float $longitude, int $radiusInKm = 10): Builder
    {
        $geoQuery = new GeoQueryBuilder($this->getConnectionName());

        return $geoQuery->withinRadius($query, 'location', $latitude, $longitude, $radiusInKm);
    }

    /**
     * Scope to find points within a rectangular bounding box.
     */
    public function scopeWithinBounds(Builder $query, float $minLat, float $minLng, float $maxLat, float $maxLng): Builder
    {
        $geoQuery = new GeoQueryBuilder($this->getConnectionName());

        return $geoQuery->withinBounds($query, 'location', $minLat, $minLng, $maxLat, $maxLng);
    }

    /**
     * Scope to find points within a circle (alias for nearby).
     */
    public function scopeWithinCircle(Builder $query, float $latitude, float $longitude, int $radiusInKm): Builder
    {
        return $this->scopeNearby($query, $latitude, $longitude, $radiusInKm);
    }

    /**
     * Scope to find points within a polygon.
     */
    public function scopeWithinPolygon(Builder $query, array $coordinates): Builder
    {
        $geoQuery = new GeoQueryBuilder($this->getConnectionName());

        return $geoQuery->withinPolygon($query, 'location', $coordinates);
    }

    /**
     * Calculate distance from this point to another point in kilometers.
     */
    public function distanceTo(float $latitude, float $longitude): float
    {
        if (! $this->latitude || ! $this->longitude) {
            return 0.0;
        }

        $geoQuery = new GeoQueryBuilder($this->getConnectionName());

        return $geoQuery->calculateDistance(
            $this->latitude,
            $this->longitude,
            $latitude,
            $longitude
        );
    }
}
