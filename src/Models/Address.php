<?php

namespace OiLab\OiLaravelGeo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;
use OiLab\OiLaravelGeo\Data\AddressData;
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
        'addressable_type',
        'addressable_id',
        'is_default',
        'latitude',
        'longitude',
        'geocoded_label',
        'geocoding_score',
        'ban_id',
        'geocoded_at',
    ];

    protected function casts(): array
    {
        return [
            'city_id' => 'integer',
            'country_id' => 'integer',
            'department_id' => 'integer',
            'region_id' => 'integer',
            'is_default' => 'boolean',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'geocoding_score' => 'decimal:3',
            'geocoded_at' => 'datetime',
        ];
    }

    /**
     * The ULID primary key cannot go through the `HasUlids` trait: that trait decides
     * the key type statically, while here it depends on `address_key_type`. A conditional
     * `use` does not exist in PHP and one subclass per case would be worse, so the three
     * pieces `HasUlids` provides are implemented by hand below.
     */
    protected static function booted(): void
    {
        static::creating(function (self $address): void {
            if (! static::usesUlidKey()) {
                return;
            }

            $keyName = $address->getKeyName();

            if (empty($address->{$keyName})) {
                $address->{$keyName} = (string) Str::ulid();
            }
        });
    }

    public static function usesUlidKey(): bool
    {
        return config('oi-laravel-geo.address_key_type', 'id') === 'ulid';
    }

    public static function isMorphable(): bool
    {
        return config('oi-laravel-geo.address_morphable', false);
    }

    public static function hasGeocodingColumns(): bool
    {
        return config('oi-laravel-geo.address_geocoding', false);
    }

    public function getKeyType(): string
    {
        return static::usesUlidKey() ? 'string' : parent::getKeyType();
    }

    public function getIncrementing(): bool
    {
        return static::usesUlidKey() ? false : parent::getIncrementing();
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

    public function addressable(): MorphTo
    {
        return $this->morphTo();
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

    public function isGeocoded(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    public function toData(): AddressData
    {
        return AddressData::fromModel($this);
    }
}
