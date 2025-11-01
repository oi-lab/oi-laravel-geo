<?php

namespace OiLab\OiLaravelGeo\Services;

use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;

/**
 * Service to build database-agnostic geospatial queries.
 *
 * Supports MySQL, PostgreSQL (PostGIS), and SQLite.
 */
class GeoQueryBuilder
{
    protected string $driver;

    protected int $srid;

    public function __construct(?string $connection = null)
    {
        $connectionName = $connection ?? config('database.default');
        $this->driver = config("database.connections.{$connectionName}.driver");
        $this->srid = config('oi-laravel-geo.srid', 4326);
    }

    /**
     * Get the appropriate driver instance for spatial queries.
     */
    protected function getDriver(): GeoQueryDriverInterface
    {
        return match ($this->driver) {
            'mysql' => new MySQLGeoDriver($this->srid),
            'pgsql' => new PostgreSQLGeoDriver($this->srid),
            'sqlite' => new SQLiteGeoDriver($this->srid),
            default => throw new InvalidArgumentException("Unsupported database driver: {$this->driver}"),
        };
    }

    /**
     * Filter points within a radius from a central point.
     */
    public function withinRadius(Builder $query, string $column, float $latitude, float $longitude, int $radiusInKm): Builder
    {
        $driver = $this->getDriver();

        return $driver->withinRadius($query, $column, $latitude, $longitude, $radiusInKm);
    }

    /**
     * Filter points within a rectangular bounding box.
     */
    public function withinBounds(Builder $query, string $column, float $minLat, float $minLng, float $maxLat, float $maxLng): Builder
    {
        $driver = $this->getDriver();

        return $driver->withinBounds($query, $column, $minLat, $minLng, $maxLat, $maxLng);
    }

    /**
     * Filter points within a circle (alias for withinRadius).
     */
    public function withinCircle(Builder $query, string $column, float $latitude, float $longitude, int $radiusInKm): Builder
    {
        return $this->withinRadius($query, $column, $latitude, $longitude, $radiusInKm);
    }

    /**
     * Filter points within a polygon.
     */
    public function withinPolygon(Builder $query, string $column, array $coordinates): Builder
    {
        $driver = $this->getDriver();

        return $driver->withinPolygon($query, $column, $coordinates);
    }

    /**
     * Filter polygons that contain a specific point.
     */
    public function polygonContainsPoint(Builder $query, string $column, float $latitude, float $longitude): Builder
    {
        $driver = $this->getDriver();

        return $driver->polygonContainsPoint($query, $column, $latitude, $longitude);
    }

    /**
     * Filter polygons that intersect with a polygon.
     */
    public function polygonIntersectsPolygon(Builder $query, string $column, array $coordinates): Builder
    {
        $driver = $this->getDriver();

        return $driver->polygonIntersectsPolygon($query, $column, $coordinates);
    }

    /**
     * Filter polygons that intersect with a bounding box.
     */
    public function polygonIntersectsBounds(Builder $query, string $column, float $minLat, float $minLng, float $maxLat, float $maxLng): Builder
    {
        $driver = $this->getDriver();

        return $driver->polygonIntersectsBounds($query, $column, $minLat, $minLng, $maxLat, $maxLng);
    }

    /**
     * Filter polygons that intersect with a circle.
     */
    public function polygonIntersectsCircle(Builder $query, string $column, float $latitude, float $longitude, int $radiusInKm): Builder
    {
        $driver = $this->getDriver();

        return $driver->polygonIntersectsCircle($query, $column, $latitude, $longitude, $radiusInKm);
    }

    /**
     * Calculate the area of a polygon in square kilometers.
     */
    public function calculatePolygonArea(string $wkt): ?float
    {
        $driver = $this->getDriver();

        return $driver->calculatePolygonArea($wkt);
    }

    /**
     * Calculate distance between two points in kilometers.
     */
    public function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $driver = $this->getDriver();

        return $driver->calculateDistance($lat1, $lng1, $lat2, $lng2);
    }
}
