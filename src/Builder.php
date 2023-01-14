<?php

namespace BradieTilley\StoryBoard;

use Illuminate\Foundation\Application;

class Builder
{
    use \Orchestra\Testbench\Concerns\CreatesApplication;

    protected static ?self $instance = null;

    protected static bool $run = false;

    public static function instance(): self
    {
        return static::$instance ??= new self();
    }

    public static function hasRun(): bool
    {
        /** @var Application $app */
        $app = app();

        return $app->hasBeenBootstrapped();
    }

    public static function run(): void
    {
        if (self::hasRun()) {
            return;
        }

        self::instance()->createApplication();
        self::$run = true;
    }
}
