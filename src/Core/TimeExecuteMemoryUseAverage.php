<?php

declare(strict_types=1);

namespace Kaspi\Benchmark\Core;

final class TimeExecuteMemoryUseAverage
{
    public function __construct(
        public readonly float $memoryUsage,
        public readonly float $memoryPeak,
        public readonly float $hrTime,
        public readonly float $iterations,
    ) {}
}
