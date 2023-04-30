<?php

namespace Tests\Fixtures;

use BradieTilley\Stories\Action;

class AnExampleActionWithProxiedData extends Action
{
    protected string $variable = 'proxiedData';

    public function __invoke(): array
    {
        $this->abc(123)->def(456)->ghi()->jkl(7, 8, 9);

        $this->internal->set('foo', 'bar');
        $this->foo = 'updated:'.$this->foo;

        return $this->internal->all();
    }
}
