# Interactive Installation Guide

This guide demonstrates the interactive installation process for the OiLaravelGeo package.

## Running the Installer

```bash
php artisan geo:install
```

## Interactive Prompts

### 1. Database Selection

```
🌍 Welcome to OiLaravelGeo Installation

Let's configure your geographic package...

┌ Which database are you using? ────────────────────────────┐
│ › MySQL 5.7+                                               │
│   PostgreSQL with PostGIS                                  │
│   SQLite                                                   │
└────────────────────────────────────────────────────────────┘
```

**What this means:**
- **MySQL 5.7+**: Best for general use with good spatial support
- **PostgreSQL with PostGIS**: Most powerful spatial features, recommended for complex geo queries
- **SQLite**: Limited spatial support, good for development/testing

---

### 2. Geometry Support

```
┌ Enable spatial/geometry support (POINT, POLYGON)? ────────┐
│ Yes / No                                                   │
└────────────────────────────────────────────────────────────┘
  Required for location-based queries and boundaries
```

**What this means:**
- **Yes**: Adds `location` (POINT) and `boundary` (POLYGON) columns to selected models
- **No**: Models will only have basic fields (name, code, etc.)

**Choose Yes if you need:**
- Location-based searches (nearby cities, within radius)
- Geographic boundaries (country borders, city limits)
- Distance calculations
- Polygon intersections

---

### 3. Select Models with Geometry (if enabled)

```
┌ Which models should have geometry support? ───────────────┐
│ ◼ Countries (boundary polygon)                             │
│ ◻ Regions (boundary polygon)                               │
│ ◻ Departments (boundary polygon)                           │
│ ◉ Cities (location point + optional boundary)              │
│ ◻ Boroughs (boundary polygon)                              │
│ ◉ Addresses (location point)                               │
└────────────────────────────────────────────────────────────┘
  Space to select, Enter to confirm
```

**What this means:**
- **Countries/Regions/Departments/Boroughs**: Get a `boundary` POLYGON column for borders
- **Cities**: Get both `location` POINT and `boundary` POLYGON columns
- **Addresses**: Get a `location` POINT column for precise GPS coordinates

**Common configurations:**
- **Minimal**: Just `Cities` and `Addresses` (for basic location features)
- **Full**: All models (for comprehensive geographic data)
- **Custom**: Select only what you need

---

### 4. Address Configuration

```
Configure Address model...

┌ Use city_id foreign key instead of city string? ──────────┐
│ Yes / No                                                   │
└────────────────────────────────────────────────────────────┘
  Recommended for relational integrity
```

**What this means:**
- **Yes**: Address uses `city_id` foreign key to `cities` table
  ```php
  $address->city_id = 1;
  $address->city->name; // "Paris"
  ```
- **No**: Address uses simple `city` string field
  ```php
  $address->city = "Paris";
  ```

**Recommendation:** Choose **Yes** for better data integrity and relationships.

---

```
┌ Add country_id foreign key to addresses? ─────────────────┐
│ Yes / No                                                   │
└────────────────────────────────────────────────────────────┘
```

**What this means:**
- **Yes**: Direct relationship to countries table
- **No**: No country relationship on addresses

---

```
┌ Add department_id foreign key to addresses? ──────────────┐
│ Yes / No                                                   │
└────────────────────────────────────────────────────────────┘
```

**What this means:**
- **Yes**: Direct relationship to departments table
- **No**: No department relationship on addresses

---

```
┌ Add region_id foreign key to addresses? ──────────────────┐
│ Yes / No                                                   │
└────────────────────────────────────────────────────────────┘
```

**What this means:**
- **Yes**: Direct relationship to regions table
- **No**: No region relationship on addresses

---

### 5. Select Models to Generate

```
┌ Which models would you like to generate in app/Models? ───┐
│ ◉ Country                                                  │
│ ◉ Region                                                   │
│ ◉ Department                                               │
│ ◉ City                                                     │
│ ◉ Borough                                                  │
│ ◉ Address                                                  │
└────────────────────────────────────────────────────────────┘
  Space to select, Enter to confirm
```

**What this means:**
Selected models will be created in `app/Models/` with:
- Appropriate traits (HasPoint, HasPolygon)
- Correct fillable fields
- Proper relationships

---

## Installation Result

After answering all prompts:

```
Generating custom migrations...
✓ Created migration: 2024_01_15_120000_create_countries_table.php
✓ Created migration: 2024_01_15_120000_create_regions_table.php
✓ Created migration: 2024_01_15_120000_create_departments_table.php
✓ Created migration: 2024_01_15_120000_create_cities_table.php
✓ Created migration: 2024_01_15_120000_create_boroughs_table.php
✓ Created migration: 2024_01_15_120000_create_addresses_table.php

Generating custom models...
✓ Created model: Country.php
✓ Created model: Region.php
✓ Created model: Department.php
✓ Created model: City.php
✓ Created model: Borough.php
✓ Created model: Address.php

✓ Configuration file updated

┌ Run migrations now? ──────────────────────────────────────┐
│ Yes / No                                                   │
└────────────────────────────────────────────────────────────┘

✅ OiLaravelGeo package installed successfully!

📝 Next steps:
  1. Review the generated migrations in database/migrations/
  2. Review the generated models in app/Models/
  3. Update config/oi-laravel-geo.php if needed
  4. Place your GeoJSON files in resources/geojson/
  5. Run: php artisan oi-laravel-geo:seed all
```

## Example Generated Files

### Example Migration (with geometry)

**database/migrations/2024_01_15_120000_create_cities_table.php**
```php
Schema::create('cities', function (Blueprint $table) {
    $table->id();
    $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
    $table->string('identifier')->unique();
    $table->string('code');
    $table->string('name');
    $table->integer('population')->nullable();
    $table->integer('surface')->nullable();
    $table->point('location')->nullable();
    $table->polygon('boundary')->nullable();
    $table->timestamps();

    $table->unique(['department_id', 'code']);
    $table->index('code');
    $table->index('name');
    $table->index('identifier');
});
```

### Example Model (with traits)

**app/Models/City.php**
```php
<?php

namespace App\Models;

use OiLab\OiLaravelGeo\Models\City as BaseCity;
use OiLab\OiLaravelGeo\Traits\HasPoint;
use OiLab\OiLaravelGeo\Traits\HasPolygon;

class City extends BaseCity
{
    use HasPoint, HasPolygon;

    protected $fillable = [
        'department_id',
        'identifier',
        'code',
        'name',
        'population',
        'surface',
        'location',
        'boundary',
    ];
}
```

## Configuration Scenarios

### Scenario 1: Basic Setup (No Geometry)

**Choices:**
- Database: MySQL 5.7+
- Enable geometry: **No**
- Address city: city_id foreign key
- Models to generate: All

**Result:**
- Standard relational database
- No spatial queries
- Simple location data via relationships

---

### Scenario 2: Full Geographic Features

**Choices:**
- Database: PostgreSQL with PostGIS
- Enable geometry: **Yes**
- Geometry models: All (Countries, Regions, Departments, Cities, Boroughs, Addresses)
- Address city: city_id foreign key
- Additional foreign keys: country_id, department_id, region_id
- Models to generate: All

**Result:**
- Full spatial support
- All models have boundary polygons
- Cities and Addresses have location points
- Complete geographic queries available

---

### Scenario 3: Location Services Only

**Choices:**
- Database: MySQL 5.7+
- Enable geometry: **Yes**
- Geometry models: Cities, Addresses
- Address city: city_id foreign key
- Models to generate: Country, Region, Department, City, Address

**Result:**
- Cities have location points and boundaries
- Addresses have location points
- Can search nearby cities/addresses
- Geographic areas without boundaries (use relationships instead)

## Common Use Cases

### E-Commerce / Delivery Service
```
Geometry: Yes
Models with geometry: Cities, Addresses
Address foreign keys: city_id, country_id
```

### Real Estate Platform
```
Geometry: Yes
Models with geometry: Regions, Cities, Addresses
Address foreign keys: city_id, region_id, country_id
```

### Government / Administrative
```
Geometry: Yes
Models with geometry: All
Address foreign keys: All
```

### Simple Directory / Contact Management
```
Geometry: No
Address foreign keys: city_id, country_id
```
