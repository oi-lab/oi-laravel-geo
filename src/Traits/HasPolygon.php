<?php

namespace OiLab\OiLaravelGeo\Traits;

use Illuminate\Database\Eloquent\Builder;
use OiLab\OiLaravelGeo\Casts\PolygonCast;
use OiLab\OiLaravelGeo\Services\GeoQueryBuilder;

/**
 * Trait for models with Polygon geometry support.
 *
 * Requires a 'boundary' column in the database.
 * - PostgreSQL: POLYGON geometry type
 * - MySQL/SQLite: JSON column
 *
 * Usage:
 * - Add 'boundary' to $fillable array
 * - The trait automatically adds the appropriate cast
 *
 * Supports MySQL, PostgreSQL (PostGIS), and SQLite.
 */
trait HasPolygon
{
    /**
     * Initialize the HasPolygon trait for an instance.
     */
    public function initializeHasPolygon(): void
    {
        $this->mergeCasts([
            'boundary' => PolygonCast::class,
        ]);
    }

    public function getBoundaryCoordinatesAttribute(): ?array
    {
        // The boundary is already cast to array by PolygonCast
        return $this->boundary;
    }

    /**
     * Scope to find polygons that contain a specific point.
     */
    public function scopeContainsPoint(Builder $query, float $latitude, float $longitude): Builder
    {
        $geoQuery = new GeoQueryBuilder($this->getConnectionName());

        return $geoQuery->polygonContainsPoint($query, 'boundary', $latitude, $longitude);
    }

    /**
     * Scope to find polygons that intersect with another polygon.
     */
    public function scopeIntersects(Builder $query, array $coordinates): Builder
    {
        $geoQuery = new GeoQueryBuilder($this->getConnectionName());

        return $geoQuery->polygonIntersectsPolygon($query, 'boundary', $coordinates);
    }

    /**
     * Scope to find polygons that intersect with a polygon (alias for intersects).
     */
    public function scopeIntersectsPolygon(Builder $query, array $coordinates): Builder
    {
        return $this->scopeIntersects($query, $coordinates);
    }

    /**
     * Scope to find polygons that intersect with a rectangular bounding box.
     */
    public function scopeIntersectsBounds(Builder $query, float $minLat, float $minLng, float $maxLat, float $maxLng): Builder
    {
        $geoQuery = new GeoQueryBuilder($this->getConnectionName());

        return $geoQuery->polygonIntersectsBounds($query, 'boundary', $minLat, $minLng, $maxLat, $maxLng);
    }

    /**
     * Scope to find polygons that intersect with a circle.
     */
    public function scopeIntersectsCircle(Builder $query, float $latitude, float $longitude, int $radiusInKm): Builder
    {
        $geoQuery = new GeoQueryBuilder($this->getConnectionName());

        return $geoQuery->polygonIntersectsCircle($query, 'boundary', $latitude, $longitude, $radiusInKm);
    }

    /**
     * Scope to find polygons that intersect with a rectangle (alias for intersectsBounds).
     */
    public function scopeIntersectsRectangle(Builder $query, float $minLat, float $minLng, float $maxLat, float $maxLng): Builder
    {
        return $this->scopeIntersectsBounds($query, $minLat, $minLng, $maxLat, $maxLng);
    }

    /**
     * Get the area of the polygon in square kilometers.
     */
    public function getAreaInSquareKilometers(): ?float
    {
        if (! $this->boundary) {
            return null;
        }

        $geoQuery = new GeoQueryBuilder($this->getConnectionName());

        return $geoQuery->calculatePolygonArea($this->boundary);
    }
}
