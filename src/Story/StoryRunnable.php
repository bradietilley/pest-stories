<?php

declare(strict_types=1);

namespace BradieTilley\StoryBoard\Story;

use BradieTilley\StoryBoard\Story;

class StoryRunnable
{
    protected int $order;

    public function __construct(
        protected Story $story,
        protected Runnable $runnable,
        protected array $arguments = [],
        int $order = null,
    ) {
        $this->order = $order ?? $runnable->getOrder();
    }

    public function withStory(Story $story): static
    {
        $this->story = $story;

        return $this;
    }

    public function getStory(): Story
    {
        return $this->story;
    }

    public function getRunnable(): Runnable
    {
        return $this->runnable;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function getOrder(): int
    {
        return $this->order;
    }

    public function register(): void
    {
        $this->runnable->register($this->getStory(), $this->getArguments());
    }

    public function boot(array $arguments = []): mixed
    {
        return $this->runnable->boot($this->getStory(), array_replace($this->getArguments(), $arguments));
    }

    public function getVariable(): string
    {
        return $this->runnable->getVariable();
    }

    public function getAppendName(): ?string
    {
        return $this->runnable->getAppendName();
    }
}
