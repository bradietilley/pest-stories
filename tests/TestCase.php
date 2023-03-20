<?php

declare(strict_types=1);

namespace Tests;

use BradieTilley\Stories\Action;
use BradieTilley\Stories\Assertion;
use BradieTilley\Stories\Helpers\CallbackRepository;
use BradieTilley\Stories\Helpers\StoryAliases;
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

        // Always reset the story aliases back to their defaults between tests
        StoryAliases::setClassAlias(Story::class, Story::class);
        StoryAliases::setClassAlias(Action::class, Action::class);
        StoryAliases::setClassAlias(Assertion::class, Assertion::class);

        // Always flush and reset the repository between tests
        CallbackRepository::flush();
    }
}
