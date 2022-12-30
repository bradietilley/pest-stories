<?php

namespace BradieTilley\StoryBoard;

use PHPUnit\Framework\TestCase;

class StoryBoard extends Story
{
    protected static bool $datasetsEnabled = false;

    /**
     * Create test cases for all tests
     */
    public function test(): self
    {
        $stories = $this->allStories();

        if (static::$datasetsEnabled) {
            $function = Story::getTestFunction();

            $function($this->getName(), function (Story $story) {
                /** @var Story $story */
                /** @var TestCase $this */

                // @codeCoverageIgnoreStart
                $story->setTest($this)->boot()->assert();
                // @codeCoverageIgnoreEnd
            })->with($stories);
        } else {
            foreach ($stories as $story) {
                $story->test();
            }
        }

        return $this;
    }

    public static function useDatasets(): void
    {
        static::$datasetsEnabled = true;
    }

    public static function dontUseDatasets(): void
    {
        static::$datasetsEnabled = false;
    }
}