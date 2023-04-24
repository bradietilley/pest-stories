<?php

declare(strict_types=1);

namespace BradieTilley\Stories\Concerns;

use BradieTilley\Stories\Action;
use BradieTilley\Stories\PendingCalls\PendingCall;
use BradieTilley\Stories\Story;
use Closure;
use Pest\Expectations\HigherOrderExpectation;

/**
 * This trait is to be added to TestCases in Pest
 * using the `uses(Stories::class)` syntax. Generally
 * you would probably run the following in `Pest.php`:
 *
 *      uses(Stories::class)->in('Feature');
 *
 * Any test suite with Stories used will inherit the
 * following helper methods which are designed to be
 * used in the following way:
 *
 *      test('my test does something')->action('do_something');
 */
trait Stories
{
    protected ?Story $story = null;

    /**
     * Reset the current instance
     */
    protected function setUpStories(): void
    {
        Story::setInstance(null);
    }

    /**
     * Get and or create the story for this test case
     */
    public function story(): Story
    {
        $this->story ??= new Story();
        Story::setInstance($this->story);

        return $this->story;
    }

    /**
     * Add an action to this story / test case
     */
    public function action(string|Closure|Action|PendingCall $action, array $arguments = [], string $variable = null): static
    {
        $this->story()->action($action, arguments: $arguments, variable: $variable);

        return $this;
    }

    /**
     * Add an expectation to this story / test case
     */
    public function expects(string|Closure $action): HigherOrderExpectation
    {
        return $this->story()->expects($action);
    }
}
