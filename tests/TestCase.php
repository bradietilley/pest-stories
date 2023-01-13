<?php

namespace Tests;

use BradieTilley\StoryBoard\StoryBoardServiceProvider;
use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    /**
     * Get package providers
     * 
     * @param Application $app
     * @return array<string> 
     */
    public function getPackageProviders($app): array
    {
        return [
            StoryBoardServiceProvider::class,
        ];
    }
}