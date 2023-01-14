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
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;

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
        return match (true) {
            in_array($name, [
                'user'
            ]) => $this->__getPerformer($name),
            in_array($name, [
                'storiesDirect',
                'storiesAll'
            ]) => $this->__getStories($name),
            default => $this->{$name},
        };
    }

    /**
     * Proxy the can/cannot methods to their setters
     *
     * @param  string  $name
     * @param  array<mixed>  $args
     */
    public function __call($name, $args): mixed
    {
        return match (true) {
            in_array($name, [
                'can',
                'cannot',
            ]) => $this->__callActions($name, $args),
            default => $this->__callMacroable($name, $args),
        };
    }

    /**
     * Proxy the can/cannot methods to their setters
     *
     * @param  string  $name
     * @param  array<mixed>  $args
     */
    public static function __callStatic($name, $args): mixed
    {
        return match (true) {
            in_array($name, [
                'can',
                'cannot',
            ]) => static::__callStaticActions($name, $args),
            default => static::__callStaticMacroable($name, $args),
        };
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
