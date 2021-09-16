<?php

namespace Migda\LaravelNestedsetVisualisation;

use Illuminate\Support\ServiceProvider;
use Migda\LaravelNestedsetVisualisation\Commands\Visualize;

class LaravelNestedsetVisualisationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerCommands();
    }

    /**
     * Register commands.
     *
     * @return void
     */
    protected function registerCommands(): void
    {
        $this->commands([
            Visualize::class,
        ]);
    }
}
