<?php

declare(strict_types=1);

namespace App;

use Kaspi\DiContainer\Interfaces\DiContainerInterface;
use Closure;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use function array_filter;
use function hrtime;
use function ltrim;
use function printf;
use function memory_get_usage;
use function memory_get_peak_usage;
use function round;
use function sprintf;
use function str_starts_with;

abstract class DoBenchAbstract
{
    private const PREFIX_BENCH_METHOD_NAME = 'doBenchmark';

    protected DiContainerInterface $container;

    /**
     * @var ReflectionMethod[]
     */
    protected array $benchmarkMethods = [];

    public function __construct(
        string $name,
        DiContainerInterface|Closure $containerOrInitializer,
    ) {
        printf("\033[32mContainer: %s\033[0m\n", $name);

        // Cheks available benchmark methods
        $methods = (new ReflectionClass($this))
            ->getMethods(ReflectionMethod::IS_PUBLIC)
        ;

        $this->benchmarkMethods = array_filter(
            $methods,
            static fn (ReflectionMethod $method) => !$method->isStatic()
                && 'doBenchmark' !== $method->getName()
                && str_starts_with($method->getName(), self::PREFIX_BENCH_METHOD_NAME)
        );

        if ($containerOrInitializer instanceof \Closure) {
            self::getFunctionMemory(function () use ($containerOrInitializer) {
                $s = hrtime(true);
                $this->container = ($containerOrInitializer)();
                self::executionTime($s, 'Initialize container.');
            });
            print "\n";
        } else {
            $this->container = $containerOrInitializer;
        }
    }

    protected static function getFunctionMemory(callable $callback): void
    {
        $startMemory = memory_get_usage();
        $startPeak = memory_get_peak_usage();

        // Execute the target function
        $callback();

        $endMemory = memory_get_usage();
        $endPeak = memory_get_peak_usage();

        printf("📊 Net retained: %s  bytes\n📊 Peak allocated: %s bytes\n", \number_format($endMemory - $startMemory), \number_format($endPeak - $startPeak));
    }

    protected static function executionTime(float $hrStart, string $labelPrefix = "", string $colorTime = "\e[31m"): void
    {
        $executionTime = (hrtime(true) - $hrStart);
        $milliseconds = round($executionTime / 1e+6, 4);

        $time = $milliseconds > 1000
            ? round(($executionTime / 1e+9), 4). ' s'
            : $milliseconds. ' ms';

        print ltrim(sprintf("%s %sTime: %s\e[0m\n", $labelPrefix, $colorTime, $time));
    }

    protected static function methodToHuman(string $methodName): string
    {
        $step1 = str_replace(['_', '-'], ' ', $methodName);
        $step2 = preg_replace('/(?<!\ )[A-Z]/', ' $0', $step1);

        return ucfirst(strtolower(trim($step2)));
    }

    /**
     * @throws ReflectionException
     */
    public function doBenchmark(): void
    {
        foreach ($this->benchmarkMethods as $method) {
            printf("\033[32m%s\033[0m\n", self::methodToHuman($method->getName()));
            $method->invoke($this);
        }
    }
}
