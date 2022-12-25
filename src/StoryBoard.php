<?php

namespace BradieTilley\StoryBoard;

class StoryBoard extends Story
{
    public function __construct()
    {
        parent::__construct();
    }

    public function createTestCase(): self
    {
        $stories = $this->all();
        
        foreach ($stories as $story) {
            $story->createTestCase();
        }

        return $this;
    }
}