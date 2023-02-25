<?php

declare(strict_types=1);

namespace BradieTilley\StoryBoard\Story;

use BradieTilley\StoryBoard\Story;

class StoryAssertion
{
    protected int $order;

    public function __construct(
        protected Story $story,
        protected Assertion $assertion,
        protected array $arguments = [],
        int $order = null,
    ) {
        $this->order = $order ?? $assertion->getOrder();
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

    public function getAssertion(): Assertion
    {
        return $this->assertion;
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
        $this->assertion->register($this->getStory(), $this->getArguments());
    }

    public function boot(array $arguments = []): mixed
    {
        return $this->assertion->boot($this->getStory(), array_replace($this->getArguments(), $arguments));
    }

    public function getVariable(): string
    {
        return $this->assertion->getVariable();
    }

    public function getAppendName(): ?string
    {
        return $this->assertion->getAppendName();
    }
}
