<?php

declare(strict_types=1);

namespace App;

use function count;
use function floor;
use function log;
use function max;
use function min;
use function round;

final class TimeExecuteMemoryUse
{
    public function __construct(
        public readonly int   $startMemory,
        private readonly int  $endMemory,
        public readonly int   $startPeak,
        public readonly int   $endPeak,
        public readonly float $hrStartTime,
        public readonly float $hrEndTime,
    )
    {
    }

    public function formatMemoryNet(int $precision = 2): string
    {
        return $this->formatBytes($this->endMemory - $this->startMemory, $precision);
    }

    public function formatMemoryPeak(int $precision = 2): string
    {
        return $this->formatBytes($this->endPeak - $this->startPeak, $precision);
    }

    public function formatTimeExecute(): string
    {
        $executionTime = $this->hrEndTime - $this->hrStartTime;
        $milliseconds = round($executionTime / 1e+6, 4);

        return $milliseconds > 1000
            ? round(($executionTime / 1e+9), 4) . ' s'
            : $milliseconds . ' ms';
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

        $bytes = max($bytes, 0);
        $pow = $bytes ? floor(log($bytes, 1024)) : 0;
        $pow = min($pow, count($units) - 1);

        $bytes /= 1024 ** $pow;

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

}
