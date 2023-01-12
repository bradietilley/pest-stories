<?php

namespace BradieTilley\StoryBoard\Story;

use Throwable;

class Result
{
    protected mixed $value = null;

    protected ?Throwable $error = null;

    private bool $valueAdded = false;

    public function setValue(mixed $value): static
    {
        $this->value = $value;
        $this->valueAdded = true;

        return $this;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function hasValue(): bool
    {
        return $this->valueAdded;
    }

    public function setError(Throwable $error): static
    {
        $this->error = $error;

        return $this;
    }

    public function getError(): ?Throwable
    {
        return $this->error;
    }

    public function errored(): bool
    {
        return $this->error !== null;
    }
}
