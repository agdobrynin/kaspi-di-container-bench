<?php

declare(strict_types=1);

namespace App;

use Closure;
use Kaspi\DiContainer\Interfaces\DiContainerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use function hrtime;
use function memory_get_peak_usage;
use function memory_get_usage;
use function printf;
use function round;
use function sprintf;
use function str_starts_with;

abstract class DoBenchAbstract
{
    private const PREFIX_BENCH_METHOD_NAME = 'doBenchmark';

    protected DiContainerInterface $container;

    /**
     * @var array<string, ReflectionMethod>
     */
    protected array $benchmarkMethods = [];

    public function __construct(
        string $name,
        DiContainerInterface|Closure $containerOrInitializer,
    ) {
        printf("\n\e[0;31mContainer: %s\033[0m\n%s\n", $name, \str_repeat('-', strlen($name)));

        // Cheks available benchmark methods
        $methods = (new ReflectionClass($this))
            ->getMethods(ReflectionMethod::IS_PUBLIC)
        ;

        foreach ($methods as $method) {
            if (!$method->isStatic()
                && 'doBenchmark' !== $method->getName()
                && str_starts_with($method->getName(), self::PREFIX_BENCH_METHOD_NAME)) {
                $attribute = $method->getAttributes(BenchmarkDescription::class)[0] ?? null;
                $description = null !== $attribute
                    ? $attribute->newInstance()->description
                    : self::methodToHuman($method->getName());
                $this->benchmarkMethods[$description] = $method;
            }
        }

        if ($containerOrInitializer instanceof \Closure) {
            $s = hrtime(true);
            self::getFunctionMemory(function () use ($containerOrInitializer) {
                $this->container = ($containerOrInitializer)();
            });
            self::executionTime($s);
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

    protected static function executionTime(float $hrStart, string $colorTime = "\e[31m"): void
    {
        $executionTime = (hrtime(true) - $hrStart);
        $milliseconds = round($executionTime / 1e+6, 4);

        $time = $milliseconds > 1000
            ? round(($executionTime / 1e+9), 4). ' s'
            : $milliseconds. ' ms';

        print sprintf("⏱️ %sTime: %s\e[0m\n", $colorTime, $time);
    }

    protected static function methodToHuman(string $methodName): string
    {
        $step1 = str_replace(['_', '-'], ' ', $methodName);
        $step2 = preg_replace('/(?<! )[A-Z]/', ' $0', $step1);

        return ucfirst(strtolower(trim($step2)));
    }

    /**
     * @throws ReflectionException
     */
    public function doBenchmark(): void
    {
        foreach ($this->benchmarkMethods as $description => $method) {
            printf("\033[32m%s\033[0m\n", $description);
            $s = hrtime(true);
            self::getFunctionMemory(fn() => $method->invoke($this));
            self::executionTime($s, "\033[33m");
            print "\n";
        }
    }
}
