<?php

namespace BradieTilley\Stories\Traits;

use BradieTilley\Stories\Callback;
use BradieTilley\Stories\Sequence;
use Closure;

trait HasSequences
{
    protected ?Sequence $sequence = null;

    public function getSequence(): Sequence
    {
        return $this->sequence ??= Sequence::make();
    }

    /**
     * @param  iterable<callback>  $callbacks
     */
    public function sequence(Closure|iterable $callbacks): static
    {
        if ($callbacks instanceof Closure) {
            $callbacks($this->getSequence());

            return $this;
        }

        $this->getSequence()->pushCallbacks($callbacks);

        return $this;
    }

    /**
     * Run all callbacks in this sequence
     */
    public function runSequence(array $arguments = []): void
    {
        if ($this->sequence === null) {
            return;
        }

        $this->sequence->boot($arguments);
    }
}
