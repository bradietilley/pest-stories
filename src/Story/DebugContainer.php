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
     */
    public function printDebug(string $level = 'debug'): void
    {
        $hierarchy = self::levelHierarchy($level);

        /** @phpstan-ignore-next-line */
        $debug = $this->filter(fn (array $data) => self::levelHierarchy($data['level']) >= $hierarchy)
            ->prepareForDumping()
            ->all();

        $dump = Config::getAliasFunction('dump');
        $dump($debug);
    }

    /**
     * Get the hierarchy of the level (lower = more verbose)
     */
    public static function levelHierarchy(string $level): int
    {
        $levels = [
            'debug',
            'info',
            'warning',
            'error',
        ];

        return array_search($level, $levels) ?: 0; // debug by default
    }
}
