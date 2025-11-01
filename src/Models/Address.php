<?php

namespace OiLab\OiLaravelGeo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OiLab\OiLaravelGeo\Facades\OiGeo;
use OiLab\OiLaravelGeo\Facades\OiLaravelGeo;

class Address extends Model
{
    protected $fillable = [
        'name',
        'street_1',
        'street_2',
        'street_3',
        'city_id',
        'city',
        'postal_code',
        'country_id',
        'department_id',
        'region_id',
    ];

    protected function casts(): array
    {
        return [
            'city_id' => 'integer',
            'country_id' => 'integer',
            'department_id' => 'integer',
            'region_id' => 'integer',
        ];
    }

    public static function hasCityRelation(): bool
    {
        return config('oi-laravel-geo.address_include_city', false);
    }

    public static function hasCountryRelation(): bool
    {
        return config('oi-laravel-geo.address_include_country', false);
    }

    public static function hasDepartmentRelation(): bool
    {
        return config('oi-laravel-geo.address_include_department', false);
    }

    public static function hasRegionRelation(): bool
    {
        return config('oi-laravel-geo.address_include_region', false);
    }

    public function getTable(): string
    {
        return config('oi-laravel-geo.tables.addresses', parent::getTable());
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(OiLaravelGeo::getCityModel());
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(OiLaravelGeo::getCountryModel());
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(OiLaravelGeo::getDepartmentModel());
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(OiLaravelGeo::getRegionModel());
    }

    public function getCityNameAttribute(): ?string
    {
        if (self::hasCityRelation()) {
            return $this->city?->name;
        }

        return $this->city;
    }

    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->name,
            $this->street_1,
            $this->street_2,
            $this->street_3,
            $this->postal_code,
            $this->city_name,
            self::hasCountryRelation() ? $this->country?->name : null,
        ]);

        return implode(', ', $parts);
    }
}
