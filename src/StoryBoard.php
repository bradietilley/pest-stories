<?php

namespace BradieTilley\StoryBoard;

use PHPUnit\Framework\TestCase;

class StoryBoard extends Story
{
    /**
     * Flag to enable datasets where the parent StoryBoard
     * becomes a Pest test case and all children stories
     * become dataset entries.
     *
     * False example:
     *      <parent test> <child test> <grandchild test>
     *
     * True example:
     *      <parent test> with dataset "<child test> <grandchild test>"
     *
     * @var bool
     */
    protected static bool $datasetsEnabled = false;

    /**
     * Create test cases for all tests
     */
    public function test(): self
    {
        if (static::$datasetsEnabled) {
            $function = Story::getTestFunction();
            $parentName = $this->getName();
            $stories = $this->allStories();

            $function($parentName, function (Story $story) {
                /** @var Story $story */
                /** @var TestCase $this */

                // @codeCoverageIgnoreStart
                $story->setTest($this)->boot()->perform();
                // @codeCoverageIgnoreEnd
            })->with($stories);
        } else {
            foreach ($this->allStories() as $story) {
                $story->test();
            }
        }

        return $this;
    }

    /**
     * Enable the use of datasets (see static::$datasetsEnabled)
     */
    public static function enableDatasets(): void
    {
        static::$datasetsEnabled = true;
    }

    /**
     * Disable the use of datasets (see static::$datasetsEnabled)
     */
    public static function disableDatasets(): void
    {
        static::$datasetsEnabled = false;
    }

    public static function datasetsEnabled(): bool
    {
        return static::$datasetsEnabled;
    }
}
