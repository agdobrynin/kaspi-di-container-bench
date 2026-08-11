<?php

declare(strict_types=1);

namespace Kaspi\Benchmark\Core;

final class TimeExecuteMemoryUseIteration
{
    public function __construct(
        public readonly int $startMemoryUsage,
        public readonly int $endMemoryUsage,
        public readonly int $startMemoryPeak,
        public readonly int $endMemoryPeak,
        public readonly float $startHrTime,
        public readonly float $endHrTime,
    ) {}

    public function memoryNet(): int
    {
        return $this->endMemoryUsage - $this->startMemoryUsage;
    }

    public function memoryPeak(): int
    {
        return $this->endMemoryPeak - $this->startMemoryPeak;
    }

    public function HrTime(): float
    {
        return $this->endHrTime - $this->startHrTime;
    }
}
