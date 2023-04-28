<?php

namespace BradieTilley\Stories\Contracts;

/**
 * Add this interface to an action that you don't want to be computed immediately.
 *
 * The callbacks (before/after) and `__invoke` method will always be deferred until
 * the story boots this action (after test case is set up), but any chained methods
 * would otherwise be invoked immediately.
 *
 * Example:
 *
 *  MyStandardAction::make()->withSomething() // 'withSomething' is immediately run
 *
 *  MyDeferredAction::make()->withSomething() // 'withSomething' is not immediately run
 */
interface Deferred
{
}
