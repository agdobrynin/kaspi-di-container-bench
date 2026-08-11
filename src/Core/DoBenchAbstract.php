<?php

declare(strict_types=1);

namespace Kaspi\Benchmark\Core;

use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use function gc_collect_cycles;
use function gc_enable;
use function hrtime;
use function memory_get_peak_usage;
use function memory_get_usage;
use function usort;

abstract class DoBenchAbstract
{
    /**
     * @var BenchMethod[]
     */
    protected array $benchmarkMethods = [];

    final public function __construct(protected readonly BenchmarkResults $benchmarkResults) {
        gc_enable();

        // Find available methods
        /** @var array<non-empty-string, ReflectionMethod> $reflectionMethods */
        $reflectionMethods = [];
        foreach ((new ReflectionClass($this))->getMethods()  as $reflectionMethod) {
            $reflectionMethods[$reflectionMethod->getName()] = $reflectionMethod;

        }

        foreach ($reflectionMethods as $methodName => $reflectionMethod) {
            $attribute = $reflectionMethod->getAttributes(Benchmark::class)[0] ?? null;

            if (null === $attribute) {
                continue;
            }

            /** @var Benchmark $attributeBenchmark */
            $attributeBenchmark = $attribute->newInstance();

            // Benchmark method must be declared with modifier public and non-static
            if (!$reflectionMethod->isPublic() || $reflectionMethod->isStatic()) {
                continue;
            }

            $description = '' !== $attributeBenchmark->description
                ? $attributeBenchmark->description
                : self::methodToHuman($methodName);

            $beforeReflectionMethod = null;

            if (null !== $attributeBenchmark->beforeMethod) {
                if (!isset($reflectionMethods[$attributeBenchmark->beforeMethod])) {
                    throw new \InvalidArgumentException(
                        sprintf('The attribute `%s` failed validation for the method `%s::%s()`. The value of the `$beforeMethod` parameter must refer to a public class method. Got value "%s".', Benchmark::class, $this::class, $methodName, $attributeBenchmark->beforeMethod)
                    );
                }

                $beforeReflectionMethod = $reflectionMethods[$attributeBenchmark->beforeMethod];
            }

            $this->benchmarkMethods[] = new BenchMethod(
                $description,
                $reflectionMethod,
                $attributeBenchmark->priority,
                $attributeBenchmark->iterations,
                $beforeReflectionMethod,
            );
        }

        usort($this->benchmarkMethods, static function (BenchMethod $a, BenchMethod $b) {
            return $b->priority <=> $a->priority;
        });
    }

    final protected static function runBenchmark(callable $callback): TimeExecuteMemoryUseIteration
    {
        $startMemoryUsage = memory_get_usage();
        $startPeakUsage = memory_get_peak_usage();
        $startHrTime = hrtime(true);

        // Execute the target function
        $callback();

        gc_collect_cycles();

        $endMemoryUsage = memory_get_usage();
        $endMemoryPeakUsage = memory_get_peak_usage();
        $endHrTime = hrtime(true);

        return new TimeExecuteMemoryUseIteration(
            $startMemoryUsage,
            $endMemoryUsage,
            $startPeakUsage,
            $endMemoryPeakUsage,
            $startHrTime,
            $endHrTime,
        );
    }

    final protected static function methodToHuman(string $methodName): string
    {
        $step1 = str_replace(['_', '-'], ' ', $methodName);
        $step2 = preg_replace('/(?<! )[A-Z]/', ' $0', $step1);

        return ucfirst(strtolower(trim($step2)));
    }

    /**
     * @throws ReflectionException
     */
    final public function doBenchmarks(): BenchmarkResults
    {
        $this->benchmarkResults->reset();

        foreach ($this->benchmarkMethods as $benchmarkMethod) {
            $benchmarkMethod->beforeReflectionMethod?->invoke($this);

            for ($i = 0; $i < $benchmarkMethod->iterations; ++$i) {
                $timeMemory = self::runBenchmark(fn() => $benchmarkMethod->reflectionMethod->invoke($this));
                $this->benchmarkResults->attach($benchmarkMethod->description, $timeMemory);
            }
        }

        return $this->benchmarkResults;
    }
}
