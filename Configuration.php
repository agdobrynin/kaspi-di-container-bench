<?php
declare(strict_types=1);

namespace Kaspi\Benchmark\Config;

enum Configuration
{
    case JsonFileResult;

    public function getValue(): mixed
    {
        return match ($this) {
            self::JsonFileResult => __DIR__ . '/var/results.json',
        };
    }
}
