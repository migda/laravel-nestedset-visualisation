<?php

namespace Migda\LaravelNestedsetVisualisation\Tests\Commands;

use Exception;
use Migda\LaravelNestedsetVisualisation\Tests\Category;
use Migda\LaravelNestedsetVisualisation\Tests\TestCase;

/**
 * Class VisualizeTest
 * @package Migda\LaravelNestedsetVisualisation\Tests\Commands
 */
class VisualizeTest extends TestCase
{
    /**
     * @test
     */
    public function it_throws_exception_when_model_not_found()
    {
        $this->expectException(Exception::class);
        $this->artisan('laravel-nestedset:visualize', [
            'model' => 'TEST',
        ]);
    }

    /**
     * @test
     */
    public function it_generates_an_image()
    {
        // Arrange
        $parent = Category::create([
            'name' => 'Drinkware',
        ]);

        $child1 = Category::create([
            'parent_id' => $parent->getKey(),
            'name' => 'Water Bottles',
        ]);

        $child2 = Category::create([
            'parent_id' => $parent->getKey(),
            'name' => 'Mugs',
        ]);

        // Act
        $outputImage = 'graph_test.jpg';

        $this->artisan('laravel-nestedset:visualize', [
            'model' => Category::class,
            'property' => 'name',
            '--output' => $outputImage,
        ]);

        // Assert
        $this->assertTrue(file_exists($outputImage));

        // Clean up
        unlink($outputImage);
    }
}
