<?php

namespace Tests\Mocks;

use BradieTilley\Stories\Action;
use BradieTilley\Stories\Contracts\InvokableCallback;
use BradieTilley\Stories\Story;

class MockInvokableAction extends Action implements InvokableCallback
{
    protected string $string = '';

    protected int $integer = 0;

    public static array $invoked = [];

    public function __invoke(Story $story, string $a, int $b): float
    {
        self::$invoked[] = [
            'story' => $story,
            'a' => $a,
            'b' => $b,
            'string' => $this->string,
            'integer' => $this->integer,
        ];

        return 0.1;
    }

    public function withString(string $value): self
    {
        $this->string = $value;

        return $this;
    }

    public function withInteger(int $value): self
    {
        $this->integer = $value;

        return $this;
    }
}
