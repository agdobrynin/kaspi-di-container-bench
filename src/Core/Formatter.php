<?php

declare(strict_types=1);

namespace Kaspi\Benchmark\Core;

use function floor;
use function log;
use function max;
use function min;
use function round;

final class Formatter
{
    /**
     * @param int $bytes memory usage in bytes
     * @param int $precision number of decimal digits to round to
     * @return string
     */
    public static function formatBytes(float $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

        $bytes = max($bytes, 0);
        $pow = $bytes ? floor(log($bytes, 1024)) : 0;
        $pow = min($pow, count($units) - 1);

        $bytes /= 1024 ** $pow;

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * @param float $hrTime microseconds
     * @param int $precision number of decimal digits to round to
     * @return string
     */
    public static function formatTimeExecute(float $hrTime, int $precision = 2): string
    {
        $milliseconds = round($hrTime / 1e+6, $precision);

        return $milliseconds > 1000
            ? round(($hrTime / 1e+9), $precision) . ' s'
            : $milliseconds . ' ms';
    }
}
