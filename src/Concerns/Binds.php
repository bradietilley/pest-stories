<?php

namespace BradieTilley\Stories\Concerns;

use function BradieTilley\Stories\Helpers\story;
use Closure;
use Pest\Support\Closure as PestClosure;

trait Binds
{
    /**
     * The preferred object to bind $this to when invoking callbacks.
     *
     * string:action = current action instance
     * string:story = current story instance
     * string:test = current test case
     * object = custom object
     */
    protected static string|object $preferBindingTo = 'action';

    /**
     * Set the preferred $this binding for callbacks to be the action (default)
     */
    public static function preferBindToAction(): void
    {
        static::$preferBindingTo = 'action';
    }

    /**
     * Set the preferred $this binding for callbacks to be the story
     */
    public static function preferBindToStory(): void
    {
        static::$preferBindingTo = 'story';
    }

    /**
     * Set the preferred $this binding for callbacks to be the test case
     */
    public static function preferBindToTest(): void
    {
        static::$preferBindingTo = 'test';
    }

    /**
     * Set the preferred $this binding for callbacks to be the test case
     */
    public static function preferBindToObject(object $newThis): void
    {
        static::$preferBindingTo = $newThis;
    }

    /**
     * @return Story|TestCase|object
     */
    public function getPreferredBinding(): object
    {
        $preferBindingTo = static::$preferBindingTo;

        if ($preferBindingTo === 'action') {
            return $this;
        }

        if ($preferBindingTo === 'story') {
            return story();
        }

        if ($preferBindingTo === 'test') {
            return story()->getTest();
        }

        /** @var object $preferBindingTo */

        return $preferBindingTo;
    }

    /**
     * Bind the given closure to the preferred bound $this
     */
    public function bindToPreferred(Closure $callback): Closure
    {
        return PestClosure::bind($callback, $this->getPreferredBinding());
    }
}
