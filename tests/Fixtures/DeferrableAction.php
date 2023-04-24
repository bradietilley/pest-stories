<?php

namespace Tests\Fixtures;

use BradieTilley\Stories\Action;
use Illuminate\Support\Collection;

class DeferrableAction extends Action
{
    protected string $name = 'deferrable_action';

    public static array $ran = [];

    public Collection $collection;

    public function __construct()
    {
        $this->collection = Collection::make();
        static::$ran[] = 'construct';

        parent::__construct();
    }

    public function __invoke(): int
    {
        static::$ran[] = 'invoke';

        return 1;
    }

    public function abc(): static
    {
        static::$ran[] = 'abc';

        return $this;
    }

    public function def(): static
    {
        static::$ran[] = 'def';

        return $this;
    }

    public function ghi(): static
    {
        static::$ran[] = 'ghi';

        return $this;
    }
}
