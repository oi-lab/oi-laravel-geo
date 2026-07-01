<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use OiLab\OiLaravelGeo\Traits\HasPoint;
use OiLab\OiLaravelGeo\Traits\HasPolygon;

beforeEach(function () {
    // Create a test model with HasPoint trait
    createPointTestTable();
    createPolygonTestTable();
});

it('can find points within radius', function () {
    $model = new class extends Model
    {
        use HasPoint;

        protected $table = 'point_test';

        protected $fillable = ['name', 'location'];

        public $timestamps = false;
    };

    // Paris coordinates: 48.8566, 2.3522
    $model->create(['name' => 'Paris', 'location' => [2.3522, 48.8566]]);

    // Lyon coordinates: 45.7640, 4.8357 (around 400km from Paris)
    $model->create(['name' => 'Lyon', 'location' => [4.8357, 45.7640]]);

    // Versailles coordinates: 48.8014, 2.1301 (around 15km from Paris)
    $model->create(['name' => 'Versailles', 'location' => [2.1301, 48.8014]]);

    // Find locations within 50km of Paris
    $nearby = $model->nearby(48.8566, 2.3522, 50)->get();

    expect($nearby)
        ->toHaveCount(2)
        ->pluck('name')->toContain('Paris')
        ->pluck('name')->toContain('Versailles')
        ->pluck('name')->not->toContain('Lyon');
});

it('can find points within bounds', function () {
    $model = new class extends Model
    {
        use HasPoint;

        protected $table = 'point_test';

        protected $fillable = ['name', 'location'];

        public $timestamps = false;
    };

    // Paris coordinates
    $model->create(['name' => 'Paris', 'location' => [2.3522, 48.8566]]);

    // London coordinates
    $model->create(['name' => 'London', 'location' => [-0.1276, 51.5074]]);

    // Berlin coordinates
    $model->create(['name' => 'Berlin', 'location' => [13.4050, 52.5200]]);

    // Bounding box around France/Paris area
    $withinBounds = $model->withinBounds(
        48.0, // minLat
        2.0,  // minLng
        49.0, // maxLat
        3.0   // maxLng
    )->get();

    expect($withinBounds)
        ->toHaveCount(1)
        ->first()->name->toBe('Paris');
});

it('can find points within circle', function () {
    $model = new class extends Model
    {
        use HasPoint;

        protected $table = 'point_test';

        protected $fillable = ['name', 'location'];

        public $timestamps = false;
    };

    $model->create(['name' => 'Paris', 'location' => [2.3522, 48.8566]]);
    $model->create(['name' => 'Lyon', 'location' => [4.8357, 45.7640]]);

    $withinCircle = $model->withinCircle(48.8566, 2.3522, 50)->get();

    expect($withinCircle)
        ->toHaveCount(1)
        ->first()->name->toBe('Paris');
});

it('can find points within polygon', function () {
    $model = new class extends Model
    {
        use HasPoint;

        protected $table = 'point_test';

        protected $fillable = ['name', 'location'];

        public $timestamps = false;
    };

    $model->create(['name' => 'Paris', 'location' => [2.3522, 48.8566]]);
    $model->create(['name' => 'London', 'location' => [-0.1276, 51.5074]]);

    // Polygon around France
    $francePolygon = [
        [-5.0, 42.0],
        [8.0, 42.0],
        [8.0, 51.0],
        [-5.0, 51.0],
    ];

    $withinPolygon = $model->withinPolygon($francePolygon)->get();

    expect($withinPolygon)
        ->toHaveCount(1)
        ->first()->name->toBe('Paris');
});

it('can calculate distance between points', function () {
    $model = new class extends Model
    {
        use HasPoint;

        protected $table = 'point_test';

        protected $fillable = ['name', 'location'];

        public $timestamps = false;
    };

    $paris = $model->create(['name' => 'Paris', 'location' => [2.3522, 48.8566]]);

    // Distance from Paris to Lyon (approximately 400km)
    $distance = $paris->distanceTo(45.7640, 4.8357);

    expect($distance)
        ->toBeGreaterThan(390)
        ->toBeLessThan(410);
});

it('can find polygons containing a point', function () {
    $model = new class extends Model
    {
        use HasPolygon;

        protected $table = 'polygon_test';

        protected $fillable = ['name', 'boundary'];

        public $timestamps = false;
    };

    // Create France boundary (simplified)
    $model->create([
        'name' => 'France',
        'boundary' => [
            [-5.0, 42.0],
            [8.0, 42.0],
            [8.0, 51.0],
            [-5.0, 51.0],
        ],
    ]);

    // Create Spain boundary (simplified)
    $model->create([
        'name' => 'Spain',
        'boundary' => [
            [-9.0, 36.0],
            [3.0, 36.0],
            [3.0, 43.0],
            [-9.0, 43.0],
        ],
    ]);

    // Paris coordinates
    $containing = $model->containsPoint(48.8566, 2.3522)->get();

    expect($containing)
        ->toHaveCount(1)
        ->first()->name->toBe('France');
});

it('can find polygons intersecting with another polygon', function () {
    $model = new class extends Model
    {
        use HasPolygon;

        protected $table = 'polygon_test';

        protected $fillable = ['name', 'boundary'];

        public $timestamps = false;
    };

    $model->create([
        'name' => 'France',
        'boundary' => [
            [-5.0, 42.0],
            [8.0, 42.0],
            [8.0, 51.0],
            [-5.0, 51.0],
        ],
    ]);

    $model->create([
        'name' => 'Italy',
        'boundary' => [
            [6.0, 36.0],
            [18.0, 36.0],
            [18.0, 47.0],
            [6.0, 47.0],
        ],
    ]);

    // Polygon overlapping France
    $overlappingPolygon = [
        [0.0, 45.0],
        [10.0, 45.0],
        [10.0, 50.0],
        [0.0, 50.0],
    ];

    $intersecting = $model->intersects($overlappingPolygon)->get();

    expect($intersecting->pluck('name'))
        ->toContain('France')
        ->toContain('Italy');
});

it('can find polygons intersecting with bounds', function () {
    $model = new class extends Model
    {
        use HasPolygon;

        protected $table = 'polygon_test';

        protected $fillable = ['name', 'boundary'];

        public $timestamps = false;
    };

    $model->create([
        'name' => 'France',
        'boundary' => [
            [-5.0, 42.0],
            [8.0, 42.0],
            [8.0, 51.0],
            [-5.0, 51.0],
        ],
    ]);

    $intersecting = $model->intersectsBounds(45.0, 0.0, 50.0, 5.0)->get();

    expect($intersecting)
        ->toHaveCount(1)
        ->first()->name->toBe('France');
});

it('can extract coordinates from point', function () {
    $model = new class extends Model
    {
        use HasPoint;

        protected $table = 'point_test';

        protected $fillable = ['name', 'location'];

        public $timestamps = false;
    };

    $paris = $model->create(['name' => 'Paris', 'location' => [2.3522, 48.8566]]);

    expect($paris)
        ->latitude->toBe(48.8566)
        ->longitude->toBe(2.3522);
});

it('can extract coordinates from polygon', function () {
    $model = new class extends Model
    {
        use HasPolygon;

        protected $table = 'polygon_test';

        protected $fillable = ['name', 'boundary'];

        public $timestamps = false;
    };

    $coordinates = [
        [-5.0, 42.0],
        [8.0, 42.0],
        [8.0, 51.0],
        [-5.0, 51.0],
    ];

    $polygon = $model->create(['name' => 'France', 'boundary' => $coordinates]);

    expect($polygon->boundary_coordinates)
        ->toBeArray()
        ->toHaveCount(4)
        ->toBe($coordinates);
});

// Helper functions to create test tables
function createPointTestTable(): void
{
    if (! Schema::hasTable('point_test')) {
        Schema::create('point_test', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('location')->nullable();
        });
    }
}

function createPolygonTestTable(): void
{
    if (! Schema::hasTable('polygon_test')) {
        Schema::create('polygon_test', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('boundary')->nullable();
        });
    }
}
