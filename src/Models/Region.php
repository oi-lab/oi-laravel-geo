<?php

namespace OiLab\OiLaravelGeo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OiLab\OiLaravelGeo\Facades\OiGeo;

class Region extends Model
{
    protected $fillable = [
        'country_id',
        'code',
        'name',
        'population',
        'surface',
    ];

    protected function casts(): array
    {
        return [
            'country_id' => 'integer',
            'population' => 'integer',
            'surface' => 'integer',
        ];
    }

    public function getTable(): string
    {
        return config('oi-laravel-geo.tables.regions', parent::getTable());
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(OiGeo::getCountryModel());
    }

    public function departments(): HasMany
    {
        return $this->hasMany(OiGeo::getDepartmentModel());
    }
}
