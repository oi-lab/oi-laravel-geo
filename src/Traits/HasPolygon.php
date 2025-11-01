<?php

namespace OiLab\OiLaravelGeo\Traits;

use Illuminate\Database\Eloquent\Builder;
use OiLab\OiLaravelGeo\Services\GeoQueryBuilder;

/**
 * Trait for models with Polygon geometry support.
 *
 * Requires a 'boundary' column of type POLYGON in the database.
 *
 * Usage:
 * - Add 'boundary' to $fillable array
 * - Add to migration: $table->polygon('boundary')->nullable();
 *
 * Supports MySQL, PostgreSQL (PostGIS), and SQLite.
 */
trait HasPolygon
{
    public function setBoundaryAttribute(?array $coordinates): void
    {
        if ($coordinates === null) {
            $this->attributes['boundary'] = null;

            return;
        }

        $points = collect($coordinates)
            ->map(fn ($point) => "{$point[0]} {$point[1]}")
            ->join(', ');

        $firstPoint = $coordinates[0];
        $points .= ", {$firstPoint[0]} {$firstPoint[1]}";

        $srid = config('oi-laravel-geo.srid', 4326);
        $this->attributes['boundary'] = "POLYGON(({$points}))";
    }

    public function getBoundaryCoordinatesAttribute(): ?array
    {
        if (! $this->boundary) {
            return null;
        }

        preg_match('/POLYGON\(\(([^)]+)\)\)/', $this->boundary, $matches);

        if (! isset($matches[1])) {
            return null;
        }

        $points = explode(', ', $matches[1]);

        return collect($points)
            ->map(function ($point) {
                [$lng, $lat] = explode(' ', $point);

                return [(float) $lng, (float) $lat];
            })
            ->slice(0, -1)
            ->values()
            ->toArray();
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
