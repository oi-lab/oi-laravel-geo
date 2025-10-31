<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('oi-laravel-geo.tables.addresses', 'addresses'), function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('street_1');
            $table->string('street_2')->nullable();
            $table->string('street_3')->nullable();

            // City: either city_id (if enabled) or city (string)
            if (config('oi-laravel-geo.address_include_city', false)) {
                $table->foreignId('city_id')->nullable()->constrained(config('oi-laravel-geo.tables.cities', 'cities'))->nullOnDelete();
                $table->index('city_id');
            } else {
                $table->string('city');
            }

            $table->string('postal_code');

            // Country: only if enabled
            if (config('oi-laravel-geo.address_include_country', false)) {
                $table->foreignId('country_id')->nullable()->constrained(config('oi-laravel-geo.tables.countries', 'countries'))->nullOnDelete();
                $table->index('country_id');
            }

            if (config('oi-laravel-geo.address_include_department', false)) {
                $table->foreignId('department_id')->nullable()->constrained(config('oi-laravel-geo.tables.departments', 'departments'))->nullOnDelete();
            }

            if (config('oi-laravel-geo.address_include_region', false)) {
                $table->foreignId('region_id')->nullable()->constrained(config('oi-laravel-geo.tables.regions', 'regions'))->nullOnDelete();
            }

            $table->timestamps();

            $table->index('postal_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('oi-laravel-geo.tables.addresses', 'addresses'));
    }
};
