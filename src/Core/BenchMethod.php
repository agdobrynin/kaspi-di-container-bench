<?php

declare(strict_types=1);

namespace Kaspi\Benchmark\Core;

use ReflectionMethod;

final class BenchMethod
{
    public function __construct(
        public readonly string $description,
        public readonly ReflectionMethod $reflectionMethod,
        public readonly int $priority = 0,
        public readonly int $iterations = 1,
        public readonly false|ReflectionMethod $beforeReflectionMethod = false,
        public readonly false|ReflectionMethod $afterReflectionMethod = false,
    ) {}
}
