<?php

namespace OiLab\OiLaravelGeo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OiLab\OiLaravelGeo\Data\BoroughData;
use OiLab\OiLaravelGeo\Facades\OiLaravelGeo;

class Borough extends Model
{
    protected $fillable = [
        'city_id',
        'code',
        'name',
        'population',
        'surface',
    ];

    protected function casts(): array
    {
        return [
            'city_id' => 'integer',
            'population' => 'integer',
            'surface' => 'integer',
        ];
    }

    public function getTable(): string
    {
        return config('oi-laravel-geo.tables.boroughs', parent::getTable());
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(OiLaravelGeo::getCityModel());
    }

    public function toData(): BoroughData
    {
        return BoroughData::fromModel($this);
    }
}
