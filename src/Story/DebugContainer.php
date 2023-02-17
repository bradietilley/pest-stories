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

    public function debug(): self
    {
        return $this->addDebug(self::LEVEL_DEBUG, ...func_get_args());
    }

    public function info(): self
    {
        return $this->addDebug(self::LEVEL_INFO, ...func_get_args());
    }

    public function warning(): self
    {
        return $this->addDebug(self::LEVEL_WARNING, ...func_get_args());
    }

    public function error(): self
    {
        return $this->addDebug(self::LEVEL_ERROR, ...func_get_args());
    }

    public function addDebug(string $level, ...$args): self
    {
        foreach ($args as $arg) {
            $this->push([
                'level' => $level,
                'time' => (new DateTime(timezone: new DateTimeZone('UTC')))->format('Y-m-d H:i:s.u'),
                'data' => $arg
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
        return $this->mapWithKeys(function (array $data) {
            $key = sprintf('[%s: %s] %s', $data['time'], Str::random(8), $data['level']);
            $value = $data['data'];

            return [
                $key => $value,
            ];
        });
    }

    public function dump(): void
    {
        dump($this->prepareForDumping());
    }
}