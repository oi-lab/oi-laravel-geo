<?php

namespace OiLab\OiLaravelGeo\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

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
        return $query->whereRaw(
            "ST_Distance_Sphere({$column}, ST_GeomFromText(?, ?)) <= ?",
            ["POINT({$longitude} {$latitude})", $this->srid, $radiusInKm * 1000]
        );
    }

    public function withinBounds(Builder $query, string $column, float $minLat, float $minLng, float $maxLat, float $maxLng): Builder
    {
        $polygon = "POLYGON(({$minLng} {$minLat}, {$maxLng} {$minLat}, {$maxLng} {$maxLat}, {$minLng} {$maxLat}, {$minLng} {$minLat}))";

        return $query->whereRaw(
            "ST_Within({$column}, ST_GeomFromText(?, ?))",
            [$polygon, $this->srid]
        );
    }

    public function withinPolygon(Builder $query, string $column, array $coordinates): Builder
    {
        $polygon = $this->buildPolygonWKT($coordinates);

        return $query->whereRaw(
            "ST_Within({$column}, ST_GeomFromText(?, ?))",
            [$polygon, $this->srid]
        );
    }

    public function polygonContainsPoint(Builder $query, string $column, float $latitude, float $longitude): Builder
    {
        return $query->whereRaw(
            "ST_Contains({$column}, ST_GeomFromText(?, ?))",
            ["POINT({$longitude} {$latitude})", $this->srid]
        );
    }

    public function polygonIntersectsPolygon(Builder $query, string $column, array $coordinates): Builder
    {
        $polygon = $this->buildPolygonWKT($coordinates);

        return $query->whereRaw(
            "ST_Intersects({$column}, ST_GeomFromText(?, ?))",
            [$polygon, $this->srid]
        );
    }

    public function polygonIntersectsBounds(Builder $query, string $column, float $minLat, float $minLng, float $maxLat, float $maxLng): Builder
    {
        $polygon = "POLYGON(({$minLng} {$minLat}, {$maxLng} {$minLat}, {$maxLng} {$maxLat}, {$minLng} {$maxLat}, {$minLng} {$minLat}))";

        return $query->whereRaw(
            "ST_Intersects({$column}, ST_GeomFromText(?, ?))",
            [$polygon, $this->srid]
        );
    }

    public function polygonIntersectsCircle(Builder $query, string $column, float $latitude, float $longitude, int $radiusInKm): Builder
    {
        // Create a buffer (circle) around the point and check for intersection
        return $query->whereRaw(
            "ST_Intersects({$column}, ST_Buffer(ST_GeomFromText(?, ?), ?))",
            ["POINT({$longitude} {$latitude})", $this->srid, $radiusInKm * 1000]
        );
    }

    public function calculatePolygonArea(string $wkt): ?float
    {
        $result = DB::selectOne(
            'SELECT ST_Area(ST_GeomFromText(?)) / 1000000 as area',
            [$wkt]
        );

        return $result ? (float) $result->area : null;
    }

    public function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $result = DB::selectOne(
            'SELECT ST_Distance_Sphere(ST_GeomFromText(?), ST_GeomFromText(?)) / 1000 as distance',
            ["POINT({$lng1} {$lat1})", "POINT({$lng2} {$lat2})"]
        );

        return $result ? (float) $result->distance : 0.0;
    }

    /**
     * Build a WKT POLYGON string from coordinates.
     */
    protected function buildPolygonWKT(array $coordinates): string
    {
        $points = collect($coordinates)
            ->map(fn ($point) => "{$point[0]} {$point[1]}")
            ->join(', ');

        $firstPoint = $coordinates[0];
        $points .= ", {$firstPoint[0]} {$firstPoint[1]}";

        return "POLYGON(({$points}))";
    }
}
