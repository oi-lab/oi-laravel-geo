<?php

namespace OiLab\OiLaravelGeo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OiLab\OiLaravelGeo\Data\DepartmentData;
use OiLab\OiLaravelGeo\Facades\OiLaravelGeo;

class Department extends Model
{
    protected $fillable = [
        'region_id',
        'code',
        'name',
        'population',
        'surface',
    ];

    protected function casts(): array
    {
        return [
            'region_id' => 'integer',
            'population' => 'integer',
            'surface' => 'integer',
        ];
    }

    public function getTable(): string
    {
        return config('oi-laravel-geo.tables.departments', parent::getTable());
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(OiLaravelGeo::getRegionModel());
    }

    public function cities(): HasMany
    {
        return $this->hasMany(OiLaravelGeo::getCityModel());
    }

    public function toData(): DepartmentData
    {
        return DepartmentData::fromModel($this);
    }
}
