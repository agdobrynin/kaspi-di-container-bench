<?php

declare(strict_types=1);

namespace Kaspi\Benchmark\Core;

use Kaspi\Benchmark\Core\Attributes\Benchmark;
use Kaspi\Benchmark\Core\Attributes\Iterations;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use function gc_collect_cycles;
use function gc_enable;
use function hrtime;
use function is_int;
use function memory_get_peak_usage;
use function memory_get_usage;
use function sprintf;
use function usort;

abstract class DoBenchAbstract
{
    /**
     * @var BenchMethod[]
     */
    protected array $benchmarkMethods = [];

    protected readonly ReflectionClass $reflectionClass;

    final public function __construct(
        protected readonly BenchmarkResults $benchmarkResults,
        protected readonly bool $showProgressBar = true,
    ) {
        gc_enable();

        $this->reflectionClass = new ReflectionClass($this);

        // Find available methods
        /** @var array<non-empty-string, ReflectionMethod> $reflectionMethods */
        $reflectionMethods = [];

        foreach ($this->reflectionClass->getMethods()  as $reflectionMethod) {
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
                $this->configureIterationMethod($attributeBenchmark),
                $beforeReflectionMethod,
            );
        }

        usort($this->benchmarkMethods, static function (BenchMethod $a, BenchMethod $b) {
            return $b->priority <=> $a->priority;
        });
    }

    final protected static function runBenchmark(callable $callback): TimeExecuteMemoryUseIteration
    {
        gc_collect_cycles();

        $startMemoryUsage = memory_get_usage();
        $startPeakUsage = memory_get_peak_usage();
        $startHrTime = hrtime(true);

        // Execute the target function
        $callback();

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
        /** @var string|null $benchmarkTitle */
        $benchmarkTitle = null;

        foreach ($this->benchmarkMethods as $benchmarkMethod) {
            $benchmarkMethod->beforeReflectionMethod?->invoke($this);

            if ($this->showProgressBar) {
                print "\n";
                $benchmarkTitle = sprintf('[%s] %s', $this->benchmarkResults->doBenchName, $benchmarkMethod->description);
            }


            for ($i = 1; $i <= $benchmarkMethod->iterations; ++$i) {
                if (null !== $benchmarkTitle) {
                    Formatter::progressBar($benchmarkTitle, $i, $benchmarkMethod->iterations);
                }

                $timeMemory = self::runBenchmark(fn() => $benchmarkMethod->reflectionMethod->invoke($this));
                $this->benchmarkResults->attach($benchmarkMethod->description, $timeMemory);
            }

            if ($this->showProgressBar) {
                print "\n";
            }
        }

        if ($this->showProgressBar) {
            print "\n";
        }

        return $this->benchmarkResults;
    }

    final protected function configureIterationMethod(Benchmark $attributeBenchmark): int
    {
        if (is_int($attributeBenchmark->iterations)
            && 0 < $attributeBenchmark->iterations) {
            return $attributeBenchmark->iterations;
        }

        if ($attributeBenchmark->iterations instanceof Iterations
            && 0 < $attributeBenchmark->iterations->iterations) {
            return $attributeBenchmark->iterations->iterations;
        }

        $iterationsAttribute = $this->reflectionClass->getAttributes(Iterations::class)[0] ?? null;

        if (null !== $iterationsAttribute) {
            /** @var Iterations $iterations */
            $iterations = $iterationsAttribute->newInstance();
            return $iterations->iterations > 0
                ? $iterations->iterations
                : 1;
        }

        return 1;
    }
}
