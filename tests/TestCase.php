<?php

declare(strict_types=1);

namespace Tests;

use BradieTilley\Stories\Laravel\StoriesServiceProvider;
use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    /**
     * Get package providers
     *
     * @param  Application  $app
     * @return array<string>
     */
    public function getPackageProviders($app): array
    {
        return [
            StoriesServiceProvider::class,
        ];
    }

    protected function setUp(): void
    {
    }
}
