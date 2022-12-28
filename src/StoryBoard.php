<?php

namespace BradieTilley\StoryBoard;

class StoryBoard extends Story
{
    /**
     * Create test cases for all tests
     */
    public function createTestCase(): self
    {
        $stories = $this->allStories();

        foreach ($stories as $story) {
            $story->createTestCase();
        }

        return $this;
    }
}
