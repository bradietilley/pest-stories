<?php

namespace Tests\Mocks;

use BradieTilley\Stories\Story;
use Closure;

class PestStoriesMockTestCall
{
    /**
     * @var null|array<int|string, iterable<Story>>
     */
    public ?array $dataset = null;

    public array $calls = [];

    public function __construct(public string $description, public Closure $callback)
    {
    }

    public function __call($name, $arguments)
    {
        $this->calls[] = [
            $name,
            $arguments,
        ];
    }

    /**
     * @param  array<\Closure|iterable<int|string, mixed>|string>  $data
     */
    public function with(array $dataset): static
    {
        $this->dataset = $dataset;

        return $this;
    }

    public function run(): void
    {
        $callback = $this->callback;

        if ($this->dataset !== null) {
            foreach ($this->dataset as $dataset) {
                $callback(...$dataset);
            }

            return;
        }

        $callback();
    }
}
