<?php

namespace OiLab\OiLaravelGeo\Traits;

use Illuminate\Database\Eloquent\Builder;
use OiLab\OiLaravelGeo\Casts\PointCast;
use OiLab\OiLaravelGeo\Services\GeoQueryBuilder;

/**
 * Trait for models with Point geometry support.
 *
 * Requires a 'location' column in the database.
 * - PostgreSQL: POINT geometry type
 * - MySQL/SQLite: JSON column
 *
 * Usage:
 * - Add 'location' to $fillable array
 * - The trait automatically adds the appropriate cast
 *
 * Supports MySQL, PostgreSQL (PostGIS), and SQLite.
 */
trait HasPoint
{
    /**
     * Initialize the HasPoint trait for an instance.
     */
    public function initializeHasPoint(): void
    {
        $this->mergeCasts([
            'location' => PointCast::class,
        ]);
    }

    public function getLatitudeAttribute(): ?float
    {
        $location = $this->location;

        if (! $location) {
            return null;
        }

        if (is_array($location)) {
            return $location['latitude'] ?? $location[1] ?? null;
        }

        return null;
    }

    public function getLongitudeAttribute(): ?float
    {
        $location = $this->location;

        if (! $location) {
            return null;
        }

        if (is_array($location)) {
            return $location['longitude'] ?? $location[0] ?? null;
        }

        return null;
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
