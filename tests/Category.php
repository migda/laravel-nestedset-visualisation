<?php

namespace Migda\LaravelNestedsetVisualisation\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kalnoy\Nestedset\NodeTrait;

/**
 * Class Category
 * @package Migda\LaravelNestedsetVisualisation\Tests
 */
class Category extends Model
{
    use SoftDeletes;
    use NodeTrait;

    /**
     * @var string[]
     */
    protected $fillable = ['name', 'parent_id'];

    /**
     * @var bool
     */
    public $timestamps = false;
}
