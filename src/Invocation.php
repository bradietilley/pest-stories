<?php

declare(strict_types=1);

namespace BradieTilley\Stories;

use BradieTilley\Stories\Helpers\StoryAliases;
use Exception;

/**
 * A pending invocation of a method, function or property (get)
 * to be later invoked.
 */
class Invocation
{
    public const TYPE_PROPERTY = 'property';

    public const TYPE_METHOD = 'method';

    public const TYPE_FUNCTION = 'function';

    public function __construct(
        public string $type,
        public string $name,
        public array $arguments = [],
        public ?object $object = null,
    ) {
    }

    /**
     * Make a new invocation
     */
    public static function make(string $type, string $name, array $arguments = [], ?object $object = null): static
    {
        $class = StoryAliases::getClassAlias(Invocation::class);

        /** @var static $class */
        $class = new $class(...func_get_args());

        return $class;
    }

    /**
     * Make a new function invocation
     */
    public static function makeFunction(string $name, array $arguments = []): static
    {
        return static::make(self::TYPE_FUNCTION, $name, $arguments);
    }

    /**
     * Make a new method invocation
     */
    public static function makeMethod(string $name, array $arguments = [], object $object = null): static
    {
        return static::make(self::TYPE_METHOD, $name, $arguments, $object);
    }

    /**
     * Make a new property invocation (getter)
     */
    public static function makeProperty(string $name, object $object = null): static
    {
        return static::make(self::TYPE_PROPERTY, $name, object: $object);
    }

    /**
     * Is this invocation a property get?
     */
    public function isProperty(): bool
    {
        return $this->type === self::TYPE_PROPERTY;
    }

    /**
     * Is this invocation a method call?
     */
    public function isMethod(): bool
    {
        return $this->type === self::TYPE_METHOD;
    }

    /**
     * Is this invocation a function call?
     */
    public function isFunction(): bool
    {
        return $this->type === self::TYPE_FUNCTION;
    }

    /**
     * Replace the name of the function, method or property.
     */
    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Replace the object for the property or method
     */
    public function setObject(?object $object = null): static
    {
        $this->object = $object;

        return $this;
    }

    /**
     * Replace the arguments for the function or method
     */
    public function setArguments(array $arguments = []): static
    {
        $this->arguments = $arguments;

        return $this;
    }

    /**
     * Run the function, method or fetch the property.
     */
    public function invoke(): mixed
    {
        if ($this->isFunction()) {
            $function = $this->name;

            if (! is_callable($function)) {
                throw new Exception('Cannot call non-callable function `%s`');
            }

            return $function(...$this->arguments);
        }

        if ($this->isMethod()) {
            $method = $this->name;

            if ($this->object === null) {
                throw new \Exception('Cannot invoke method %s on null object');
            }

            return $this->object->{$method}(...$this->arguments);
        }

        if ($this->isProperty()) {
            $method = $this->name;

            if ($this->object === null) {
                throw new \Exception('Cannot get property %s on null object');
            }

            return $this->object->{$method};
        }

        throw new \Exception('Unsupported invocation type %s');
    }

    /**
     * Compile the invocation data to array for testing purposes
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'name' => $this->name,
            'arguments' => $this->arguments,
            'object' => $this->object,
        ];
    }
}
