<?php

namespace OiLab\OiLaravelGeo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OiLab\OiLaravelGeo\Facades\OiGeo;

class City extends Model
{
    protected $fillable = [
        'department_id',
        'identifier',
        'code',
        'name',
        'population',
        'surface',
    ];

    protected function casts(): array
    {
        return [
            'department_id' => 'integer',
            'population' => 'integer',
            'surface' => 'integer',
        ];
    }

    public function getTable(): string
    {
        return config('oi-laravel-geo.tables.cities', parent::getTable());
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(OiGeo::getDepartmentModel());
    }

    public function boroughs(): HasMany
    {
        return $this->hasMany(OiGeo::getBoroughModel());
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(OiGeo::getAddressModel());
    }
}
