<?php

declare(strict_types=1);

namespace BradieTilley\StoryBoard\Story;

use BradieTilley\StoryBoard\Story;

class StoryAction
{
    protected int $order;

    public function __construct(
        protected Story $story,
        protected Action $action,
        protected array $arguments = [],
        int $order = null,
    ) {
        $this->order = $order ?? $action->getOrder();
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

    public function getAction(): Action
    {
        return $this->action;
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
        $this->action->register($this->getStory(), $this->getArguments());
    }

    public function boot(array $arguments = []): mixed
    {
        return $this->action->boot($this->getStory(), array_replace($this->getArguments(), $arguments));
    }

    public function getVariable(): string
    {
        return $this->action->getVariable();
    }

    public function getAppendName(): ?string
    {
        return $this->action->getAppendName();
    }
}
