<?php

use BradieTilley\StoryBoard\Traits\RunOnce;

test('an object with RunOnce trait will not run the same function twice', function () {
    $class = new class ()
    {
        use RunOnce;

        public function foo(): bool
        {
            return $this->alreadyRun('foo');
        }

        public function bar(): bool
        {
            return $this->alreadyRun('bar');
        }
    };

    expect($class->foo())->toBeFalse();
    expect($class->foo())->toBeTrue();
    expect($class->foo())->toBeTrue();
    expect($class->bar())->toBeFalse();
    expect($class->bar())->toBeTrue();
    expect($class->bar())->toBeTrue();
});
