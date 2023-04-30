<?php

namespace Tests\Fixtures;

use BradieTilley\Stories\Action;

class AnExampleActionWithMethods extends Action
{
    protected int $abc = 0;

    protected int $def = 0;

    public function __invoke(): void
    {
        $this->set('abc', $this->abc);
        $this->set('def', $this->def);
    }

    public function abc(int $abc): self
    {
        $this->abc = $abc;

        return $this;
    }

    public function def(int $def): self
    {
        $this->def = $def;

        return $this;
    }
}
