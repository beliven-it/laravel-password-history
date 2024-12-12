<?php

use Beliven\PasswordHistory\Tests\TestCase;
use Beliven\PasswordHistory\Traits\HasPasswordHistory;
use Illuminate\Database\Eloquent\Model;

uses(TestCase::class)->in(__DIR__);

class TestModel extends Model
{
    protected $guarded = [];

    public $timestamps = false;

    protected $table = 'test_models';
}

class TestModelWithTrait extends Model
{
    use HasPasswordHistory;

    protected $guarded = [];

    public $timestamps = false;

    protected $table = 'test_models';
}

class TestModelWithCast extends Model
{
    use HasPasswordHistory;

    protected $guarded = [];

    public $timestamps = false;

    protected $table = 'test_models';

    protected $casts = [
        'password' => 'hashed',
    ];
}
