<?php

namespace OiLab\OiLaravelGeo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OiLab\OiLaravelGeo\Facades\OiGeo;

class Country extends Model
{
    protected $fillable = [
        'code',
        'name',
        'population',
        'surface',
    ];

    protected function casts(): array
    {
        return [
            'population' => 'integer',
            'surface' => 'integer',
        ];
    }

    public function getTable(): string
    {
        return config('oi-laravel-geo.tables.countries', parent::getTable());
    }

    public function regions(): HasMany
    {
        return $this->hasMany(OiGeo::getRegionModel());
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(OiGeo::getAddressModel());
    }
}
