<?php

namespace BradieTilley\StoryBoard\Traits;

trait HasSingleRunner
{
    /**
     * Container of already-run
     *
     * @var array
     */
    private array $alreadyRun = [];

    /**
     * Determine if this is the first time the given identifier
     * action is run on this object.
     */
    public function alreadyRun(string $identifier): bool
    {
        $alreadyRun = in_array($identifier, $this->alreadyRun);

        // Prevent this from running again
        $this->alreadyRun[] = $identifier;

        return $alreadyRun;
    }
}
