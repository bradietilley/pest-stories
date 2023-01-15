<?php

namespace BradieTilley\StoryBoard\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

/**
 * Supply some deferred context to the given object (story)
 * through means of setting existing cache entries, session
 * values, or configuration.
 *
 * @mixin \BradieTilley\StoryBoard\Contracts\WithInheritance
 */
trait HasPendingContext
{
    /**
     * Repository of pending config and session changes to be
     * made for this action/story.
     */
    protected array $pendingContext = [];

    /**
     * Record some pending context
     */
    private function setPendingContext(string $context, string|array $key, mixed $value = null): static
    {
        $data = is_array($key) ? $key : [$key => $value];
        $data = array_replace_recursive($this->pendingContext[$context] ?? [], $data);

        $this->pendingContext[$context] = $data;

        return $this;
    }

    /**
     * Set some cache when this object is ran
     *
     * Deferred proxy to
     *      Cache::set($key, $value)
     */
    public function setCache(string|array $key, mixed $value = null): static
    {
        return $this->setPendingContext('cache', $key, $value);
    }

    /**
     * Set some config when this object is ran
     *
     * Deferred proxy to
     *      Config::set($key, $value)
     */
    public function setConfig(string|array $key, mixed $value = null): static
    {
        return $this->setPendingContext('config', $key, $value);
    }

    /**
     * Set some session values when this object is ran
     *
     * Deferred proxy to
     *      Session::set($key, $value)
     */
    public function setSession(string|array $key, mixed $value = null): static
    {
        return $this->setPendingContext('session', $key, $value);
    }

    /**
     * Inherit any pending context data
     */
    public function inheritPendingContext(): void
    {
        $all = [];

        foreach (array_reverse($this->getAncestors()) as $level) {
            $all = array_replace_recursive($all, (array) $level->getProperty('pendingContext'));
        }

        $this->pendingContext = $all;
    }

    /**
     * Boot the pending context data
     */
    public function bootPendingContext(): void
    {
        if (! empty($data = $this->allPendingContext()['cache'] ?? [])) {
            foreach ($data as $key => $value) {
                Cache::set($key, $value);
            }
        }

        if (! empty($data = $this->allPendingContext()['config'] ?? [])) {
            foreach ($data as $key => $value) {
                Config::set($key, $value);
            }
        }

        if (! empty($data = $this->allPendingContext()['session'] ?? [])) {
            foreach ($data as $key => $value) {
                Session::put($key, $value);
            }
        }
    }

    /**
     * Get all registered config
     */
    public function allPendingContext(): array
    {
        return $this->pendingContext;
    }
}
