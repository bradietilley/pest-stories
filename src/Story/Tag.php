<?php

declare(strict_types=1);

namespace BradieTilley\StoryBoard\Story;

use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\Traits\HasCallbacks;
use BradieTilley\StoryBoard\Traits\HasName;
use BradieTilley\StoryBoard\Traits\HasOrder;
use BradieTilley\StoryBoard\Traits\HasSingleRunner;
use Closure;
use Stringable;

class Tag implements Stringable
{
    use HasName;
    use HasOrder;
    use HasCallbacks;
    use HasSingleRunner;

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

    /**
     * Create a new tag
     */
    public static function make(string $name, Closure|string|int|float|bool|null $value, ?int $order = null): static
    {
        $class = Config::getAliasClass('tag', Tag::class);

        /** @var static $tag */
        $tag = new $class($name, $value, $order);

        return $tag;
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
            $value = $this->runCallback('value', $story->getParameters());

            if (! is_null($value) && ! is_bool($value) && ! is_numeric($value)) {
                /** @phpstan-ignore-next-line */
                $value = (string) $value;
            }

            $this->value = $value;
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

        if (($name !== null) && ($name === $value)) {
            return $name;
        }

        if (is_null($value) || is_bool($value)) {
            $value = json_encode($value);
        }

        return sprintf('%s: %s', $name, $value);
    }

    public function getValue(): string|float|int|bool|null
    {
        return $this->value;
    }
}
