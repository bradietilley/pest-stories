<?php

declare(strict_types=1);

namespace BradieTilley\StoryBoard\Contracts;

use BradieTilley\StoryBoard\Story\Runnable;
use BradieTilley\StoryBoard\Story\StoryRunnable;

/**
 * This object has actions, and assertions
 *
 * @mixin WithInheritance
 */
interface WithRunnables
{
    /**
     * Get all regsitered assertions for this story (no inheritance lookup)
     *
     * @return array<int, StoryRunnable>
     */
    public function getRunnablesByType(string $type): array;

    /**
     * Add the given runnable to this object (story)
     */
    public function pushRunnable(Runnable $runnable, array $arguments = []): static;
}
