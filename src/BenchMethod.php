<?php

declare(strict_types=1);

namespace App;

use ReflectionMethod;

final class BenchMethod
{
    public function __construct(
        public readonly string $description,
        public readonly ReflectionMethod $method,
        public readonly int $priority = 0,
        public readonly int $iterations = 1,
    ) {}
}
