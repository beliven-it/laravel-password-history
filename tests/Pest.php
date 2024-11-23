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

class TestModelWihTrait extends Model
{
    use HasPasswordHistory;

    protected $guarded = [];

    public $timestamps = false;

    protected $table = 'test_models';
}
