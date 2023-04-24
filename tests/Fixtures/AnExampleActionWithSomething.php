<?php

namespace Tests\Fixtures;

use BradieTilley\Stories\Action;

class AnExampleActionWithSomething extends Action
{
    protected string $something = 'default';

    public static array $ran = [];

    public function __invoke(): void
    {
        static::$ran[] = 'something:'.$this->something;
    }

    public function withSomething(string $something): static
    {
        $this->something = $something;

        return $this;
    }
}
