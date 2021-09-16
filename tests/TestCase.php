<?php

namespace Migda\LaravelNestedsetVisualisation\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as Orchestra;
use Migda\LaravelNestedsetVisualisation\LaravelNestedsetVisualisationServiceProvider;

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
            fn(string $modelName) => 'Migda\\LaravelNestedsetVisualisation\\Database\\Factories\\' . class_basename($modelName) . 'Factory'
        );
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

        // Migrations
        include_once __DIR__ . '/database/migrations/create_categories_table.php';
        (new \CreateCategoriesTable())->up();

    }
}
