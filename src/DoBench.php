<?php

declare(strict_types=1);

namespace App;

abstract class DoBench
{
    protected static function getFunctionMemory(callable $callback): void
    {
        $startMemory = memory_get_usage();
        $startPeak = memory_get_peak_usage();

        // Execute the target function
        $callback();

        $endMemory = memory_get_usage();
        $endPeak = memory_get_peak_usage();

        \printf("📊 Net retained: %s  bytes\n📊 Peak allocated: %s bytes\n", \number_format($endMemory - $startMemory), \number_format($endPeak - $startPeak));
    }

    protected static function executionTime(float $hrStart, string $labelPrefix = "", string $colorTime = "\e[31m"): void
    {
        $executionTime = (hrtime(true) - $hrStart);
        $milliseconds = \round($executionTime / 1e+6, 4);

        $time = $milliseconds > 1000
            ? \round(($executionTime / 1e+9), 4). ' sec'
            : $milliseconds. ' µ sec';

        print \ltrim(\sprintf("%s %sTime: %s\e[0m\n", $labelPrefix, $colorTime, $time));
    }

    abstract public static function doBench(string $name, \Kaspi\DiContainer\Interfaces\DiContainerInterface $container): void;
}
