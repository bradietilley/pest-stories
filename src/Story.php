<?php

namespace BradieTilley\StoryBoard;

use BradieTilley\StoryBoard\Contracts\WithActions;
use BradieTilley\StoryBoard\Contracts\WithCallbacks;
use BradieTilley\StoryBoard\Contracts\WithData;
use BradieTilley\StoryBoard\Contracts\WithInheritance;
use BradieTilley\StoryBoard\Contracts\WithIsolation;
use BradieTilley\StoryBoard\Contracts\WithName;
use BradieTilley\StoryBoard\Contracts\WithNameShortcuts;
use BradieTilley\StoryBoard\Contracts\WithPendingContext;
use BradieTilley\StoryBoard\Contracts\WithPerformer;
use BradieTilley\StoryBoard\Contracts\WithSingleRunner;
use BradieTilley\StoryBoard\Contracts\WithStories;
use BradieTilley\StoryBoard\Contracts\WithTags;
use BradieTilley\StoryBoard\Contracts\WithTest;
use BradieTilley\StoryBoard\Contracts\WithTestCaseShortcuts;
use BradieTilley\StoryBoard\Contracts\WithTimeout;
use BradieTilley\StoryBoard\Traits\HasActions;
use BradieTilley\StoryBoard\Traits\HasCallbacks;
use BradieTilley\StoryBoard\Traits\HasData;
use BradieTilley\StoryBoard\Traits\HasInheritance;
use BradieTilley\StoryBoard\Traits\HasIsolation;
use BradieTilley\StoryBoard\Traits\HasName;
use BradieTilley\StoryBoard\Traits\HasNameShortcuts;
use BradieTilley\StoryBoard\Traits\HasPendingContext;
use BradieTilley\StoryBoard\Traits\HasPerformer;
use BradieTilley\StoryBoard\Traits\HasSingleRunner;
use BradieTilley\StoryBoard\Traits\HasStories;
use BradieTilley\StoryBoard\Traits\HasTags;
use BradieTilley\StoryBoard\Traits\HasTest;
use BradieTilley\StoryBoard\Traits\HasTestCaseShortcuts;
use BradieTilley\StoryBoard\Traits\HasTimeout;
use Closure;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;

/**
 * @property-read Collection<int,Story> $storiesDirect
 * @property-read Collection<string,Story> $storiesAll
 * @property-read ?Authenticatable $user
 *
 * @method self can(string|Closure|null $name = null, string|Closure|null $assertion = null) Named arguments not supported (magic)
 * @method self cannot(string|Closure|null $name = null, string|Closure|null $assertion = null) Named arguments not supported (magic)
 * @method static self can(string|Closure|null $name = null, string|Closure|null $assertion = null) Named arguments not supported (magic)
 * @method static self cannot(string|Closure|null $name = null, string|Closure|null $assertion = null) Named arguments not supported (magic)
 */
class Story implements WithActions, WithCallbacks, WithData, WithInheritance, WithIsolation, WithName, WithNameShortcuts, WithPendingContext, WithPerformer, WithSingleRunner, WithStories, WithTimeout, WithTags, WithTest, WithTestCaseShortcuts
{
    use Conditionable;
    use HasCallbacks;
    use HasData;
    use HasName;
    use HasNameShortcuts;
    use HasInheritance;
    use HasIsolation;
    use HasPendingContext;
    use HasPerformer;
    use HasActions;
    use HasSingleRunner;
    use HasStories;
    use HasTags;
    use HasTest;
    use HasTestCaseShortcuts;
    use HasTimeout;
    use Macroable {
        __call as __callMacroable;
        __callStatic as __callStaticMacroable;
    }

    public readonly int $id;

    private static int $idCounter = 0;

    public function __construct(protected ?string $name = null, protected ?Story $parent = null)
    {
        $this->id = ++self::$idCounter;
    }

    /**
     * Proxy certain property getters to methods
     *
     * @param  string  $name
     * @return mixed
     */
    public function __get($name)
    {
        if ($name === 'storiesDirect') {
            return $this->collectGetStories();
        }

        if ($name === 'storiesAll') {
            return $this->collectAllStories();
        }

        if ($name === 'user') {
            return $this->getUser();
        }

        return $this->{$name};
    }

    /**
     * Proxy the can/cannot methods to their setters
     *
     * @param  string  $method
     * @param  array<mixed>  $parameters
     */
    public function __call($method, $parameters): mixed
    {
        if ($method === 'can' || $method === 'cannot') {
            $method = 'set'.ucfirst($method);

            return $this->{$method}(...$parameters);
        }

        return $this->__callMacroable($method, $parameters);
    }

    /**
     * Proxy the can/cannot methods to their setters
     *
     * @param  string  $method
     * @param  array<mixed>  $parameters
     */
    public static function __callStatic($method, $parameters): mixed
    {
        if ($method === 'can' || $method === 'cannot') {
            return self::make()->{$method}(...$parameters);
        }

        return static::__callStaticMacroable($method, $parameters);
    }

    /**
     * Create a new story
     */
    public static function make(?string $name = null, ?Story $parent = null): static
    {
        /** @phpstan-ignore-next-line */
        return new static($name, $parent);
    }

    /**
     * Get parameters available for DI callbacks
     *
     * @return array
     */
    public function getParameters(array $additional = []): array
    {
        $data = array_replace($this->allData(), [
            'story' => $this,
            'test' => $this->getTest(),
            'can' => $this->can,
            'user' => $this->getUser(),
            'result' => $this->getResult(),
        ], $additional);

        return $data;
    }
}
