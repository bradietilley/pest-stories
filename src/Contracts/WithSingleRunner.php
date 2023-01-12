<?php

namespace BradieTilley\StoryBoard\Contracts;

interface WithSingleRunner
{
    /**
     * Determine if this is the first time the given identifier
     * action is run on this object.
     */
    public function alreadyRun(string $identifier): bool;
}
