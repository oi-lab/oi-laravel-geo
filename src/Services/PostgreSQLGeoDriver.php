<?php

namespace OiLab\OiLaravelGeo\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * PostgreSQL/PostGIS-specific implementation for geospatial queries.
 *
 * Requires PostGIS extension to be installed.
 */
class PostgreSQLGeoDriver implements GeoQueryDriverInterface
{
    public function __construct(protected int $srid = 4326) {}

    public function withinRadius(Builder $query, string $column, float $latitude, float $longitude, int $radiusInKm): Builder
    {
        return $query->whereRaw(
            "ST_DWithin({$column}::geography, ST_SetSRID(ST_MakePoint(?, ?), ?)::geography, ?)",
            [$longitude, $latitude, $this->srid, $radiusInKm * 1000]
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
            "ST_Contains({$column}, ST_SetSRID(ST_MakePoint(?, ?), ?))",
            [$longitude, $latitude, $this->srid]
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
        return $query->whereRaw(
            "ST_Intersects({$column}::geography, ST_Buffer(ST_SetSRID(ST_MakePoint(?, ?), ?)::geography, ?))",
            [$longitude, $latitude, $this->srid, $radiusInKm * 1000]
        );
    }

    public function calculatePolygonArea(string $wkt): ?float
    {
        $result = DB::selectOne(
            'SELECT ST_Area(ST_GeomFromText(?)::geography) / 1000000 as area',
            [$wkt]
        );

        return $result ? (float) $result->area : null;
    }

    public function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $result = DB::selectOne(
            'SELECT ST_Distance(
                ST_SetSRID(ST_MakePoint(?, ?), ?)::geography,
                ST_SetSRID(ST_MakePoint(?, ?), ?)::geography
            ) / 1000 as distance',
            [$lng1, $lat1, $this->srid, $lng2, $lat2, $this->srid]
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
