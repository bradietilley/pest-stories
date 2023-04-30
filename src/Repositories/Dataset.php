<?php

namespace BradieTilley\Stories\Repositories;

use function BradieTilley\Stories\Helpers\story;

class Dataset extends DataRepository
{
    /**
     * Map the dataset keys into story variables
     *
     * @param  array<string, string>  $map
     */
    public function mapIntoStory(array $map): static
    {
        $merge = [];

        foreach ($this as $key => $value) {
            $mapped = $map[$key] ?? $key;

            $merge[$mapped] = $value;
        }

        story()->merge($merge);

        return $this;
    }
}
