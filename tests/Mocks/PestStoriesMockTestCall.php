<?php

namespace Tests\Mocks;

use BradieTilley\Stories\Story;
use BradieTilley\Stories\Traits\TestCallProxies;
use Closure;

class PestStoriesMockTestCall
{
    use TestCallProxies;

    /**
     * @var null|array<int|string, iterable<Story>>
     */
    public ?array $dataset = null;

    public function __construct(public string $description, public Closure $callback)
    {
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

    public function testCallProxies(): array
    {
        return $this->testCallProxies;
    }
}
