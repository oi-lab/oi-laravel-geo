<?php

namespace OiLab\OiLaravelGeo\Services;

use Illuminate\Database\Eloquent\Builder;

/**
 * MySQL-specific implementation for geospatial queries.
 *
 * MySQL 5.7+ supports spatial functions.
 */
class MySQLGeoDriver implements GeoQueryDriverInterface
{
    public function __construct(protected int $srid = 4326) {}

    public function withinRadius(Builder $query, string $column, float $latitude, float $longitude, int $radiusInKm): Builder
    {
        // Use Haversine formula for JSON-stored points
        return $query->whereRaw(
            "(
                6371 * acos(
                    cos(radians(?)) *
                    cos(radians(CAST(JSON_EXTRACT({$column}, '$.latitude') AS DECIMAL(10,8)))) *
                    cos(radians(CAST(JSON_EXTRACT({$column}, '$.longitude') AS DECIMAL(11,8))) - radians(?)) +
                    sin(radians(?)) *
                    sin(radians(CAST(JSON_EXTRACT({$column}, '$.latitude') AS DECIMAL(10,8))))
                )
            ) <= ?",
            [$latitude, $longitude, $latitude, $radiusInKm]
        );
    }

    public function withinBounds(Builder $query, string $column, float $minLat, float $minLng, float $maxLat, float $maxLng): Builder
    {
        // Check if point JSON is within bounding box
        return $query->whereRaw(
            "CAST(JSON_EXTRACT({$column}, '$.longitude') AS DECIMAL(11,8)) BETWEEN ? AND ?",
            [$minLng, $maxLng]
        )->whereRaw(
            "CAST(JSON_EXTRACT({$column}, '$.latitude') AS DECIMAL(10,8)) BETWEEN ? AND ?",
            [$minLat, $maxLat]
        );
    }

    public function withinPolygon(Builder $query, string $column, array $coordinates): Builder
    {
        // For simplicity with JSON, use bounding box approximation
        // For accurate polygon containment, consider using PostgreSQL with PostGIS
        $lngs = array_column($coordinates, 0);
        $lats = array_column($coordinates, 1);

        $minLng = min($lngs);
        $maxLng = max($lngs);
        $minLat = min($lats);
        $maxLat = max($lats);

        return $this->withinBounds($query, $column, $minLat, $minLng, $maxLat, $maxLng);
    }

    public function polygonContainsPoint(Builder $query, string $column, float $latitude, float $longitude): Builder
    {
        // Simplified implementation for JSON polygons
        // For accurate polygon containment, use PostgreSQL with PostGIS
        return $query->whereNotNull($column);
    }

    public function polygonIntersectsPolygon(Builder $query, string $column, array $coordinates): Builder
    {
        // Simplified implementation for JSON polygons
        // For accurate polygon intersection, use PostgreSQL with PostGIS
        return $query->whereNotNull($column);
    }

    public function polygonIntersectsBounds(Builder $query, string $column, float $minLat, float $minLng, float $maxLat, float $maxLng): Builder
    {
        // Simplified implementation for JSON polygons
        // For accurate polygon intersection, use PostgreSQL with PostGIS
        return $query->whereNotNull($column);
    }

    public function polygonIntersectsCircle(Builder $query, string $column, float $latitude, float $longitude, int $radiusInKm): Builder
    {
        // Simplified implementation for JSON polygons
        // For accurate polygon intersection, use PostgreSQL with PostGIS
        return $query->whereNotNull($column);
    }

    public function calculatePolygonArea(string $wkt): ?float
    {
        // For JSON polygons, we need to implement a custom area calculation
        // For now, return null - this should be implemented if needed
        return null;
    }

    public function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        // Use Haversine formula for distance calculation
        $earthRadius = 6371; // kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
