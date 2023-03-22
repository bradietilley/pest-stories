<?php

declare(strict_types=1);

namespace Tests;

use BradieTilley\Stories\Action;
use BradieTilley\Stories\Assertion;
use BradieTilley\Stories\Laravel\StoriesServiceProvider;
use BradieTilley\Stories\Story;
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
        parent::setUp();

        Action::flushMacros();
        Assertion::flushMacros();
        Story::flushMacros();
    }
}
