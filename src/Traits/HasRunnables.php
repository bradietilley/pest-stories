<?php

declare(strict_types=1);

namespace BradieTilley\StoryBoard\Traits;

use BradieTilley\StoryBoard\Story\Runnable;
use BradieTilley\StoryBoard\Story\StoryRunnable;
use Closure;

/**
 * This object has actions, and assertions
 *
 * @mixin Story
 */
trait HasRunnables
{
    protected array $runnables = [];

    /**
     * Get all regsitered assertions for this story (no inheritance lookup)
     *
     * @return array<int, StoryRunnable>
     */
    public function getRunnablesByType(string $type): array
    {
        return $this->runnables[$type] ?? [];
    }

    /**
     * Add the given runnable to this object (story)
     */
    public function pushRunnable(Runnable $runnable, array $arguments = [], int $order = null): static
    {
        $key = $runnable::getAliasName();

        $storyRunnable = new StoryRunnable(
            $this,
            $runnable,
            $arguments,
            $order,
        );

        $this->runnables[$key] ??= [];
        $this->runnables[$key][] = $storyRunnable;

        return $this;
    }

    public function pushRunnableByClass(string $runnableClass, string|Closure|Runnable $runnable, array $arguments = [], int $order = null): static
    {
    }
}
