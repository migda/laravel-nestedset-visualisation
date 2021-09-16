<?php

namespace Migda\LaravelNestedsetVisualisation\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Application;
use Migda\LaravelNestedsetVisualisation\LaravelNestedsetVisualisationServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

/**
 * Class TestCase
 * @package Migda\LaravelNestedsetVisualisation\Tests
 */
class TestCase extends Orchestra
{
    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Migda\\LaravelNestedsetVisualisation\\Database\\Factories\\' . class_basename($modelName) . 'Factory'
        );

        // Run migration for testing
        $this->loadMigrationsFrom([
            '--path' => realpath(__DIR__ . '/database/migrations'),
        ]);
        $this->artisan('migrate');
    }

    /**
     * @param Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            LaravelNestedsetVisualisationServiceProvider::class,
        ];
    }

    /**
     * @param Application $app
     *
     * @return array
     */
    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}
