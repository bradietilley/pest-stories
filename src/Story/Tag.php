<?php

namespace BradieTilley\StoryBoard\Story;

use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\Traits\HasCallbacks;
use BradieTilley\StoryBoard\Traits\HasName;
use BradieTilley\StoryBoard\Traits\HasOrder;
use BradieTilley\StoryBoard\Traits\RunOnce;
use Closure;
use Stringable;

class Tag implements Stringable
{
    use HasName;
    use HasOrder;
    use HasCallbacks;
    use RunOnce;

    protected string|int|float|bool|null $value = null;

    public function __construct(protected string $name, Closure|string|int|float|bool|null $value, protected ?int $order = null)
    {
        $this->order ??= self::getNextOrder();

        if ($value instanceof Closure) {
            $this->setCallback('value', $value);
        } else {
            $this->value = $value;
        }
    }

    public function __toString(): string
    {
        return $this->getTag();
    }

    public function register(Story $story): self
    {
        if ($this->alreadyRun('register')) {
            return $this;
        }

        return $this;
    }

    public function boot(Story $story): self
    {
        if ($this->alreadyRun('boot')) {
            return $this;
        }

        if ($this->hasCallback('value')) {
            $this->value = $this->runCallback('value', $story->getParameters());
        }

        return $this;
    }

    public function value(Story $story): string|int|float|bool|null
    {
        return $this->value = $this->boot($story)->value;
    }

    public function getTag(): string
    {
        $name = $this->getName();
        $value = $this->value;

        if ($name === $value) {
            return $name;
        }

        if (is_null($value) || is_bool($value)) {
            $value = json_encode($value);
        }

        return sprintf('%s: %s', $name, $value);
    }

    public function getValue(): string|float|int|null
    {
        return $this->value;
    }
}
