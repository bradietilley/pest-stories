<?php

namespace BradieTilley\Stories\Concerns;

use BradieTilley\Stories\Action;
use function BradieTilley\Stories\Helpers\story;
use Closure;

/**
 * @mixin Action
 */
trait Events
{
    /**
     * @var array<string, array<Closure>>
     */
    protected array $callbacks = [];

    /**
     * Record a callback to run later
     */
    public function callbackSave(string $name, Closure $callback): static
    {
        $this->callbacks[$name] ??= [];
        $this->callbacks[$name][] = $callback;

        return $this;
    }

    /**
     * Run all callbacks registered under the given event name
     */
    public function callbackRun(string $name): void
    {
        $story = story();

        foreach ($this->callbacks[$name] ?? [] as $callback) {
            $story->callCallback($callback, [
                'action' => $this,
            ]);
        }
    }

    /**
     * Run this callback before this action gets called
     */
    public function before(Closure $callback): static
    {
        return $this->callbackSave(__FUNCTION__, $callback);
    }

    /**
     * Run this callback after this action gets called
     */
    public function after(Closure $callback): static
    {
        return $this->callbackSave(__FUNCTION__, $callback);
    }
}
