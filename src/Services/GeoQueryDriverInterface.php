<?php

namespace OiLab\OiLaravelGeo\Services;

use Illuminate\Database\Eloquent\Builder;

/**
 * Interface for database-specific geospatial query implementations.
 */
interface GeoQueryDriverInterface
{
    /**
     * Filter points within a radius from a central point.
     */
    public function withinRadius(Builder $query, string $column, float $latitude, float $longitude, int $radiusInKm): Builder;

    /**
     * Filter points within a rectangular bounding box.
     */
    public function withinBounds(Builder $query, string $column, float $minLat, float $minLng, float $maxLat, float $maxLng): Builder;

    /**
     * Filter points within a polygon.
     */
    public function withinPolygon(Builder $query, string $column, array $coordinates): Builder;

    /**
     * Filter polygons that contain a specific point.
     */
    public function polygonContainsPoint(Builder $query, string $column, float $latitude, float $longitude): Builder;

    /**
     * Filter polygons that intersect with a polygon.
     */
    public function polygonIntersectsPolygon(Builder $query, string $column, array $coordinates): Builder;

    /**
     * Filter polygons that intersect with a bounding box.
     */
    public function polygonIntersectsBounds(Builder $query, string $column, float $minLat, float $minLng, float $maxLat, float $maxLng): Builder;

    /**
     * Filter polygons that intersect with a circle.
     */
    public function polygonIntersectsCircle(Builder $query, string $column, float $latitude, float $longitude, int $radiusInKm): Builder;

    /**
     * Calculate the area of a polygon in square kilometers.
     */
    public function calculatePolygonArea(string $wkt): ?float;

    /**
     * Calculate distance between two points in kilometers.
     */
    public function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float;
}
