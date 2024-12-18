<?php

namespace Beliven\PasswordHistory\Tests;

use Beliven\PasswordHistory\PasswordHistoryServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
// use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    // use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        //$this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        Schema::create('test_models', function ($table) {
            $table->id();
            $table->string('password')->nullable();
            $table->string('name')->nullable();
            $table->timestamps();
        });

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Beliven\\PasswordHistory\\Database\\Factories\\' . class_basename($modelName) . 'Factory'
        );
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('test_models');
        Schema::dropIfExists('password_hashes');

        parent::tearDown();
    }

    protected function getPackageProviders($app)
    {
        return [
            PasswordHistoryServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $migration = include __DIR__ . '/../database/migrations/create_password-history_table.php.stub';
        $migration->up();

    }
}
