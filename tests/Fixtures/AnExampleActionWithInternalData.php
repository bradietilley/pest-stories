<?php

namespace Tests\Fixtures;

use BradieTilley\Stories\Action;

class AnExampleActionWithInternalData extends Action
{
    public function __invoke(): void
    {
        // Story has foo = 123
        expect($this->has('foo'))->toBeTrue();
        expect($this->get('foo'))->toBe(123);

        // Action does not have foo
        expect($this->internal->has('foo'))->toBeFalse();

        // Set internal foo
        $this->internal->set('foo', 456);

        // Story sstill has foo = 123
        expect($this->has('foo'))->toBeTrue();
        expect($this->get('foo'))->toBe(123);

        // Action now has foo = 456
        expect($this->internal->has('foo'))->toBeTrue();
        expect($this->internal->get('foo'))->toBe(456);
    }
}
