<?php

namespace BradieTilley\StoryBoard\Traits;

use Illuminate\Container\Container;

trait HasContainer
{
    public function getContainer(): Container
    {
        return Container::getInstance();
    }

    public function call($callback, array $arguments): mixed
    {
        return $this->getContainer()->call($callback, $arguments);
    }
}
