<?php

namespace BradieTilley\StoryBoard;

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
        return self::$run;
    }

    public static function run(): void
    {
        self::instance()->createApplication();
        
        self::$run = true;
    }
}