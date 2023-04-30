<?php

declare(strict_types=1);

namespace BradieTilley\Stories\Concerns;

use BradieTilley\Stories\Action;
use BradieTilley\Stories\Exceptions\ProxyDataUnknownClassTypeException;
use BradieTilley\Stories\Repositories\DataRepository;
use BradieTilley\Stories\Story;

/**
 * Proxies calls to the data repository setter
 */
trait ProxiesData
{
    /**
     * Determine what data repository should be used.
     */
    public function getProxyDataRepository(): DataRepository
    {
        if ($this instanceof Story) {
            return $this->data;
        }

        /** @phpstan-ignore-next-line */
        if ($this instanceof Action) {
            return $this->internal;
        }

        throw ProxyDataUnknownClassTypeException::make($this);
    }

    /**
     * Proxy all unknown method calls to store the data in the respective
     * data repository.
     *
     * Example:
     *
     *     ->action(CreateUser::make()->role('admin'))
     *     ->
     *
     * @param  string  $name The method (i.e. variable) name
     * @param  array<mixed>  $arguments The arguments (i.e. value(s))
     */
    public function __call($name, $arguments): static
    {
        if (count($arguments) === 0) {
            $this->getProxyDataRepository()->set($name, true);

            return $this;
        }

        if (count($arguments) === 1) {
            $this->getProxyDataRepository()->set($name, $arguments[0]);

            return $this;
        }

        $this->getProxyDataRepository()->set($name, $arguments);

        return $this;
    }

    /**
     * Proxy a property set to a data repository get.
     *
     * Example:
     *
     *     public function __invoke(): int
     *     {
     *         $this->internal->set('abc', 123);
     *
     *         return $this->abc; // 123
     *     }
     *
     * @param  string  $name
     * @param  mixed  $value
     */
    public function __set($name, $value): void
    {
        $this->getProxyDataRepository()->set($name, $value);
    }

    /**
     * Proxy a property get to a data repository get.
     *
     * Example:
     *
     *     ->action(CreateUser::make(), variable: 'user')
     *     ->action(DoSomething::make()->user)
     *
     *     // The ->user will read the 'user' from the story
     *
     * @param  string  $name
     */
    public function __get($name): mixed
    {
        return $this->getProxyDataRepository()->get($name);
    }
}
