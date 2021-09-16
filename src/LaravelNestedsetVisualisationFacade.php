<?php

namespace Migda\LaravelNestedsetVisualisation;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Migda\LaravelNestedsetVisualisation\LaravelNestedsetVisualisation
 */
class LaravelNestedsetVisualisationFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laravel-nestedset-visualisation';
    }
}
