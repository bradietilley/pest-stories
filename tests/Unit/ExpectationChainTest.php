<?php

use BradieTilley\Stories\Exceptions\ExpectationChainStoryRequiredException;
use BradieTilley\Stories\ExpectationChain;

test('a chain must have a story bound in order to cast back to a story', function () {
    $chain = ExpectationChain::make();
    $chain->toBe('X');

    $chain->story();
})->throws(ExpectationChainStoryRequiredException::class);
