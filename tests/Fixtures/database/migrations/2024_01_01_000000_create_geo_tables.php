<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->integer('population')->nullable();
            $table->integer('surface')->nullable();
            $table->json('boundary')->nullable();
            $table->timestamps();

            $table->index('name');
        });

        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained('countries')->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->integer('population')->nullable();
            $table->integer('surface')->nullable();
            $table->json('boundary')->nullable();
            $table->timestamps();

            $table->unique(['country_id', 'code']);
            $table->index('code');
            $table->index('name');
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->constrained('regions')->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->integer('population')->nullable();
            $table->integer('surface')->nullable();
            $table->json('boundary')->nullable();
            $table->timestamps();

            $table->unique(['region_id', 'code']);
            $table->index('code');
            $table->index('name');
        });

        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->string('identifier')->unique();
            $table->string('code');
            $table->string('name');
            $table->integer('population')->nullable();
            $table->integer('surface')->nullable();
            $table->json('location')->nullable();
            $table->json('boundary')->nullable();
            $table->timestamps();

            $table->unique(['department_id', 'code']);
            $table->index('code');
            $table->index('name');
        });

        Schema::create('boroughs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained('cities')->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->integer('population')->nullable();
            $table->integer('surface')->nullable();
            $table->json('boundary')->nullable();
            $table->timestamps();

            $table->unique(['city_id', 'code']);
            $table->index('code');
            $table->index('name');
        });

        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('street_1');
            $table->string('street_2')->nullable();
            $table->string('street_3')->nullable();
            $table->string('city')->nullable();
            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->string('postal_code');
            $table->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('region_id')->nullable()->constrained('regions')->nullOnDelete();
            $table->json('location')->nullable();
            $table->string('addressable_type')->nullable();
            $table->string('addressable_id', 36)->nullable();
            $table->boolean('is_default')->default(false);
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('geocoded_label', 255)->nullable();
            $table->decimal('geocoding_score', 4, 3)->nullable();
            $table->string('ban_id', 32)->nullable();
            $table->timestamp('geocoded_at')->nullable();
            $table->timestamps();

            $table->index('postal_code');
            $table->index(['addressable_type', 'addressable_id']);
        });

        // Same shape as `addresses`, but with a ULID primary key, so both
        // `address_key_type` values can be exercised in one test suite.
        Schema::create('ulid_addresses', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name')->nullable();
            $table->string('street_1');
            $table->string('street_2')->nullable();
            $table->string('street_3')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code');
            $table->string('addressable_type')->nullable();
            $table->string('addressable_id', 36)->nullable();
            $table->boolean('is_default')->default(false);
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('geocoded_label', 255)->nullable();
            $table->decimal('geocoding_score', 4, 3)->nullable();
            $table->string('ban_id', 32)->nullable();
            $table->timestamp('geocoded_at')->nullable();
            $table->timestamps();

            $table->index(['addressable_type', 'addressable_id']);
        });

        // Two address holders with deliberately different key types: this is what
        // makes `addressable_id` a string(36) rather than a morphs() column.
        Schema::create('test_users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('test_entities', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_entities');
        Schema::dropIfExists('test_users');
        Schema::dropIfExists('ulid_addresses');
        Schema::dropIfExists('addresses');
        Schema::dropIfExists('boroughs');
        Schema::dropIfExists('cities');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('regions');
        Schema::dropIfExists('countries');
    }
};
