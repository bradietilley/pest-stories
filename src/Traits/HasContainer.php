<?php

namespace BradieTilley\StoryBoard\Traits;

use Closure;
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

    public function callOptional(?Closure $callback, array $arguments = []): mixed
    {
        if ($callback === null) {
            return null;
        }

        return $this->call($callback, $arguments);
    }
}
