<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('oi-laravel-geo.tables.cities', 'cities'), function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained(config('oi-laravel-geo.tables.departments', 'departments'))->cascadeOnDelete();
            $table->string('identifier')->unique();
            $table->string('code');
            $table->string('name');
            $table->integer('population')->nullable();
            $table->integer('surface')->nullable();
            $table->timestamps();

            $table->unique(['department_id', 'code']);
            $table->index('code');
            $table->index('name');
            $table->index('identifier');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('oi-laravel-geo.tables.cities', 'cities'));
    }
};
