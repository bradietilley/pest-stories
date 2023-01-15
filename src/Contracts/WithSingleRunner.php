<?php

namespace BradieTilley\StoryBoard\Contracts;

/**
 * Restricts certain actions from being run more than once when
 * you check via the `alreadyRun()` method.
 *
 * Supports multiple 'run once' checks by using different `$identifier`
 */
interface WithSingleRunner
{
    /**
     * Determine if this is the first time the given identifier
     * action is run on this object. Running this will
     * automatically flag this identifier as being run.
     */
    public function alreadyRun(string $identifier): bool;
}
