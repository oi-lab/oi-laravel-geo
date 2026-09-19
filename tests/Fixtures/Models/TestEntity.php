<?php

namespace OiLab\OiLaravelGeo\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use OiLab\OiLaravelGeo\Concerns\HasAddresses;

/**
 * Address holder with a ULID primary key — shares the addresses table with
 * {@see TestUser}, whose key is a bigint.
 */
class TestEntity extends Model
{
    use HasAddresses, HasUlids;

    protected $table = 'test_entities';

    protected $fillable = ['name'];
}
