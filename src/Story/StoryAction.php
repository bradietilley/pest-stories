<?php

namespace BradieTilley\StoryBoard\Story;

use BradieTilley\StoryBoard\Story;

class StoryAction
{
    public function __construct(
        protected Story $story,
        protected Action $action,
        protected array $arguments = [],
        protected ?int $order = null,
    )
    {
        $this->order ??= $action->getOrder();
    }

    public function withStory(Story $story): self
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
        $this->action->register($this->story, $this->arguments);
    }

    public function boot(array $arguments = []): mixed
    {
        return $this->action->boot($this->story, array_replace($this->arguments, $arguments));
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