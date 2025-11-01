<?php

namespace OiLab\OiLaravelGeo\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

abstract class AbstractGeoJsonImporter
{
    public function import(string $filePath): int
    {
        $this->validateFile($filePath);

        $geojson = $this->loadGeoJson($filePath);

        $this->validateGeoJsonFormat($geojson);

        return $this->importFeatures($geojson['features']);
    }

    protected function validateFile(string $filePath): void
    {
        if (! File::exists($filePath)) {
            throw new \RuntimeException("GeoJSON file not found: {$filePath}");
        }
    }

    protected function loadGeoJson(string $filePath): array
    {
        return json_decode(File::get($filePath), true);
    }

    protected function validateGeoJsonFormat(array $geojson): void
    {
        if (! isset($geojson['features'])) {
            throw new \RuntimeException('Invalid GeoJSON format: missing features');
        }
    }

    protected function importFeatures(array $features): int
    {
        $count = 0;

        DB::transaction(function () use ($features, &$count) {
            foreach ($features as $feature) {
                $properties = $feature['properties'] ?? [];

                if ($this->importFeature($properties)) {
                    $count++;
                }
            }
        });

        return $count;
    }

    abstract protected function importFeature(array $properties): bool;
}
