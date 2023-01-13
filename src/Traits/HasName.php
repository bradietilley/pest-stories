<?php

namespace BradieTilley\StoryBoard\Traits;

use BradieTilley\StoryBoard\Contracts\WithActions;
use BradieTilley\StoryBoard\Contracts\WithInheritance;
use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\Story\Config;
use Illuminate\Support\Str;

/**
 * @property ?string $name
 *
 * @mixin \BradieTilley\StoryBoard\Contracts\WithInheritance
 */
trait HasName
{
    protected array $fullName = [];

    public function __cloneName(): void
    {
        $this->name = Str::random(32);
    }

    /**
     * Alias for setName()
     */
    public function name(string $name): static
    {
        return $this->setName($name);
    }

    /**
     * Set the name (or name fragment) of this story
     */
    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the name (or name fragment) of this story
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Get the name (or name fragment) of this story.
     */
    public function getNameString(): string
    {
        return (string) $this->name;
    }

    public function inheritName(): void
    {
        if (! $this instanceof WithInheritance) {
            return;
        }

        $datasetKey = Config::datasetsEnabled() ? 'dataset' : 'default';

        if (isset($this->fullName[$datasetKey])) {
            return;
        }

        $name = [];
        $levels = array_reverse($this->getAncestors());
        $first = array_key_first($levels);

        foreach ($levels as $key => $level) {
            if ($key === $first) {
                if (Config::datasetsEnabled()) {
                    continue;
                }
            }

            $name[] = $level->getLevelName();
        }

        $name = trim((string) preg_replace('/\s+/', ' ', implode(' ', $name)));

        $this->fullName[$datasetKey] = $name;
    }

    public function getFullName(): string
    {
        $datasetKey = Config::datasetsEnabled() ? 'dataset' : 'default';

        return $this->fullName[$datasetKey] ?? $this->getName();
    }

    public function getLevelName(): string
    {
        $name = $this->getNameString();

        /**
         * Append names from actions (where actions opt to `->appendName()`)
         */
        if ($this instanceof WithActions) {
            $name = "{$name} {$this->getNameFromActions()}";
        }

        $name = trim($name);

        return $name;
    }
}
