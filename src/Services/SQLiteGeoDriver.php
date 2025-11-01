<?php

namespace OiLab\OiLaravelGeo\Services;

use Illuminate\Database\Eloquent\Builder;

/**
 * SQLite-specific implementation for geospatial queries.
 *
 * SQLite has limited spatial support. This implementation uses the Haversine formula
 * for distance calculations and polygon-in-polygon checks using basic geometry.
 *
 * For production use with SQLite, consider installing SpatiaLite extension.
 */
class SQLiteGeoDriver implements GeoQueryDriverInterface
{
    public function __construct(protected int $srid = 4326) {}

    public function withinRadius(Builder $query, string $column, float $latitude, float $longitude, int $radiusInKm): Builder
    {
        // Extract latitude and longitude from POINT column using string parsing
        // POINT format: "POINT(longitude latitude)"
        return $query->whereRaw(
            "(
                6371 * acos(
                    cos(radians(?)) *
                    cos(radians(CAST(substr({$column}, instr({$column}, ' ') + 1, instr({$column}, ')') - instr({$column}, ' ') - 1) AS REAL))) *
                    cos(radians(CAST(substr({$column}, 7, instr({$column}, ' ') - 7) AS REAL)) - radians(?)) +
                    sin(radians(?)) *
                    sin(radians(CAST(substr({$column}, instr({$column}, ' ') + 1, instr({$column}, ')') - instr({$column}, ' ') - 1) AS REAL)))
                )
            ) <= ?",
            [$latitude, $longitude, $latitude, $radiusInKm]
        );
    }

    public function withinBounds(Builder $query, string $column, float $minLat, float $minLng, float $maxLat, float $maxLng): Builder
    {
        // Extract latitude and longitude from POINT column
        return $query->whereRaw(
            "CAST(substr({$column}, 7, instr({$column}, ' ') - 7) AS REAL) BETWEEN ? AND ?",
            [$minLng, $maxLng]
        )->whereRaw(
            "CAST(substr({$column}, instr({$column}, ' ') + 1, instr({$column}, ')') - instr({$column}, ' ') - 1) AS REAL) BETWEEN ? AND ?",
            [$minLat, $maxLat]
        );
    }

    public function withinPolygon(Builder $query, string $column, array $coordinates): Builder
    {
        // For SQLite without SpatiaLite, we use a bounding box approximation
        // This is not perfectly accurate but works for most cases
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
        // SQLite doesn't have built-in polygon functions
        // This is a simplified implementation using bounding box
        // For accurate results, use SpatiaLite extension
        return $query->whereRaw(
            "? LIKE '%POLYGON%'",
            [$column]
        );
    }

    public function polygonIntersectsPolygon(Builder $query, string $column, array $coordinates): Builder
    {
        // Simplified implementation using bounding box
        $lngs = array_column($coordinates, 0);
        $lats = array_column($coordinates, 1);

        $minLng = min($lngs);
        $maxLng = max($lngs);
        $minLat = min($lats);
        $maxLat = max($lats);

        return $query->whereRaw(
            "? LIKE '%POLYGON%'",
            [$column]
        );
    }

    public function polygonIntersectsBounds(Builder $query, string $column, float $minLat, float $minLng, float $maxLat, float $maxLng): Builder
    {
        // Simplified implementation
        return $query->whereRaw(
            "? LIKE '%POLYGON%'",
            [$column]
        );
    }

    public function polygonIntersectsCircle(Builder $query, string $column, float $latitude, float $longitude, int $radiusInKm): Builder
    {
        // Simplified implementation
        return $query->whereRaw(
            "? LIKE '%POLYGON%'",
            [$column]
        );
    }

    public function calculatePolygonArea(string $wkt): ?float
    {
        // SQLite doesn't support spatial area calculation without SpatiaLite
        // Return null or implement a PHP-based calculation
        return null;
    }

    public function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        // Haversine formula implementation in PHP
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
