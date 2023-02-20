<?php

declare(strict_types=1);

namespace BradieTilley\StoryBoard\Traits;

use function BradieTilley\StoryBoard\debug;

/**
 * Restricts certain things from being run more than once when
 * you check via the `alreadyRun()` method.
 *
 * Supports multiple 'run once' checks by using different `$identifier`
 */
trait HasSingleRunner
{
    /**
     * Container of already-run
     *S
     *
     * @var array<int, string> (value = that have run)
     */
    private array $alreadyRun = [];

    /**
     * Determine if this is the first time the given identifier
     * action is run on this object. Running this will
     * automatically flag this identifier as being run.
     */
    public function alreadyRun(string $identifier): bool
    {
        $alreadyRun = $this->alreadyRunSafe($identifier);

        // Prevent this from running again
        $this->alreadyRun[] = $identifier;

        debug(
            sprintf(
                'Checking if `%s` has already run: %s',
                $identifier,
                $alreadyRun ? 'Already run' : 'First time running',
            ),
        );

        return $alreadyRun;
    }

    /**
     * Determine if this is the first time the given identifier
     * action is run on this object. Running this will NOT
     * automatically flag this identifier as being run unlike
     * `alreadyRun()`
     */
    public function alreadyRunSafe(string $identifier): bool
    {
        $alreadyRun = in_array($identifier, $this->alreadyRun);

        return $alreadyRun;
    }
}
