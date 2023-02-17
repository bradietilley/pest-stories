<?php

namespace BradieTilley\StoryBoard\Story;

use DateTime;
use DateTimeZone;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DebugContainer extends Collection
{
    public const LEVEL_DEBUG = 'debug';

    public const LEVEL_INFO = 'info';

    public const LEVEL_WARNING = 'warning';

    public const LEVEL_ERROR = 'error';

    public static ?DebugContainer $instance = null;

    public function debug(mixed ...$arguments): self
    {
        return $this->addDebug(self::LEVEL_DEBUG, ...$arguments);
    }

    public function info(mixed ...$arguments): self
    {
        return $this->addDebug(self::LEVEL_INFO, ...$arguments);
    }

    public function warning(mixed ...$arguments): self
    {
        return $this->addDebug(self::LEVEL_WARNING, ...$arguments);
    }

    public function error(mixed ...$arguments): self
    {
        return $this->addDebug(self::LEVEL_ERROR, ...$arguments);
    }

    public function addDebug(string $level, mixed ...$arguments): self
    {
        foreach ($arguments as $argument) {
            $this->push([
                'level' => $level,
                'time' => (new DateTime(timezone: new DateTimeZone('UTC')))->format('Y-m-d H:i:s.u'),
                'data' => $argument,
            ]);
        }

        return $this;
    }

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    public static function flush(): void
    {
        self::$instance = null;
    }

    public static function swap(DebugContainer $instance): void
    {
        self::$instance = $instance;
    }

    public function prepareForDumping(): self
    {
        /** @phpstan-ignore-next-line */
        return $this->mapWithKeys(function (array $data) {
            $key = sprintf('[%s: %s] %s', $data['time'], Str::random(8), $data['level']);
            $value = $data['data'];

            return [
                $key => $value,
            ];
        });
    }

    /**
     * Dump the container
     *
     * @phpstan-ignore-next-line
     */
    public function dump(): void
    {
        dump($this->prepareForDumping()->all());
    }
}
