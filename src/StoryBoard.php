<?php

namespace BradieTilley\StoryBoard;


class StoryBoard extends Story
{
    /**
     * Create a new storyboard.
     * 
     * A storyboard has no parent unlike Story
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Create test cases for all tests
     */
    public function createTestCase(): self
    {
        $stories = $this->all();
        
        foreach ($stories as $story) {
            $story->createTestCase();
        }

        return $this;
    }
}