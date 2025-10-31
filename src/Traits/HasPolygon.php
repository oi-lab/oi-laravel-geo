<?php

namespace OiLab\OiLaravelGeo\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Trait for models with Polygon geometry support.
 *
 * Requires a 'boundary' column of type POLYGON in the database.
 *
 * Usage:
 * - Add 'boundary' to $fillable array
 * - Add to migration: $table->polygon('boundary')->nullable();
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
        if (!$this->boundary) {
            return null;
        }

        preg_match('/POLYGON\(\(([^)]+)\)\)/', $this->boundary, $matches);

        if (!isset($matches[1])) {
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

    public function scopeContainsPoint(Builder $query, float $latitude, float $longitude): Builder
    {
        $srid = config('oi-laravel-geo.srid', 4326);

        return $query->whereRaw(
            "ST_Contains(boundary, ST_GeomFromText(?, ?))",
            ["POINT({$longitude} {$latitude})", $srid]
        );
    }

    public function scopeIntersects(Builder $query, array $coordinates): Builder
    {
        $srid = config('oi-laravel-geo.srid', 4326);

        $points = collect($coordinates)
            ->map(fn ($point) => "{$point[0]} {$point[1]}")
            ->join(', ');

        $firstPoint = $coordinates[0];
        $points .= ", {$firstPoint[0]} {$firstPoint[1]}";

        $polygon = "POLYGON(({$points}))";

        return $query->whereRaw(
            "ST_Intersects(boundary, ST_GeomFromText(?, ?))",
            [$polygon, $srid]
        );
    }

    public function getAreaInSquareKilometers(): ?float
    {
        if (!$this->boundary) {
            return null;
        }

        $result = \DB::selectOne(
            "SELECT ST_Area(ST_GeomFromText(?)) / 1000000 as area",
            [$this->boundary]
        );

        return $result ? (float) $result->area : null;
    }
}
