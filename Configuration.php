<?php
declare(strict_types=1);

namespace Kaspi\Benchmark\Config;

enum Configuration
{
    case JsonFileResult;
    case MaxIndexOfService;

    public function getValue(): mixed
    {
        return match ($this) {
            self::JsonFileResult => __DIR__ . '/var/results.json',
            self::MaxIndexOfService => 1_000,
        };
    }
}
