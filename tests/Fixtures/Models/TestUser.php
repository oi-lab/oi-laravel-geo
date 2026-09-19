<?php

namespace OiLab\OiLaravelGeo\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use OiLab\OiLaravelGeo\Concerns\HasAddresses;

/**
 * Address holder with an auto-incrementing bigint primary key.
 */
class TestUser extends Model
{
    use HasAddresses;

    protected $table = 'test_users';

    protected $fillable = ['name'];
}
