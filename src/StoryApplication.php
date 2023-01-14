<?php

namespace BradieTilley\StoryBoard;

use Illuminate\Foundation\Application;

/**
 * Story creation/registration may require access to the
 * Laravel Application. As such, we may need to boot the
 * application (once) as part of the first Story's boot
 * process.
 */
class StoryApplication
{
    use \Orchestra\Testbench\Concerns\CreatesApplication;

    public static function booted(): bool
    {
        /** @var Application $app */
        $app = app();

        return $app->hasBeenBootstrapped();
    }

    public static function boot(): void
    {
        if (self::booted()) {
            return;
        }

        (new self())->createApplication();
    }
}
