<?php

namespace BradieTilley\StoryBoard\Traits;

trait RunOnce
{
    /**
     * Container of already-run
     * @var array
     */
    private array $runOnce = [];

    /**
     * Determine if this is the first time the given identifier
     * action is run on this object.
     */
    public function alreadyRun(string $identifier): bool
    {
        $alreadyRun = in_array($identifier, $this->runOnce);

        // Prevent this from running again
        $this->runOnce[] = $identifier;

        return $alreadyRun;
    }
}