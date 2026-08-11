<?php

declare(strict_types=1);

namespace Kaspi\Benchmark\Core;

use Kaspi\DiContainer\Interfaces\DiContainerBuilderInterface;
use Kaspi\DiContainer\Interfaces\DiContainerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use function array_unshift;
use function hrtime;
use function memory_get_peak_usage;
use function memory_get_usage;
use function usort;

abstract class DoBenchAbstract
{
    protected DiContainerInterface $container;

    /**
     * @var BenchMethod[]
     */
    protected array $benchmarkMethods = [];

    final public function __construct(
        protected readonly DiContainerBuilderInterface $containerBuilder,
        protected readonly BenchmarkResults $benchmarkResults,
        protected readonly string $buildContainerBenchmarkDescription = 'Build container',
    ) {
        // Find available methods
        /** @var array<non-empty-string, ReflectionMethod> $reflectionMethods */
        $reflectionMethods = [];
        foreach ((new ReflectionClass($this))->getMethods()  as $reflectionMethod) {
            if ($reflectionMethod->isPublic() && !$reflectionMethod->isStatic()) {
                $reflectionMethods[$reflectionMethod->getName()] = $reflectionMethod;
            }
        }

        foreach ($reflectionMethods as $methodName => $reflectionMethod) {
            $attribute = $reflectionMethod->getAttributes(Benchmark::class)[0] ?? null;

            if (null === $attribute) {
                continue;
            }

            /** @var Benchmark $attributeBenchmark */
            $attributeBenchmark = $attribute->newInstance();

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

        array_unshift(
            $this->benchmarkMethods,
            new BenchMethod(
                $this->buildContainerBenchmarkDescription,
                new ReflectionMethod($this, 'buildContainer'),
            )
        );
    }

    final protected static function getFunctionMemory(callable $callback): TimeExecuteMemoryUseIteration
    {
        $startMemory = memory_get_usage();
        $startPeak = memory_get_peak_usage();
        $hrStart = hrtime(true);

        // Execute the target function
        $callback();

        $endMemory = memory_get_usage();
        $endPeak = memory_get_peak_usage();
        $hrEnd = hrtime(true);

        return new TimeExecuteMemoryUseIteration(
            $startMemory,
            $endMemory,
            $startPeak,
            $endPeak,
            $hrStart,
            $hrEnd,
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
    final public function doBenchmark(): BenchmarkResults
    {
        $this->benchmarkResults->reset();

        foreach ($this->benchmarkMethods as $benchmarkMethod) {
            $benchmarkMethod->beforeReflectionMethod?->invoke($this);

            for ($i = 0; $i < $benchmarkMethod->iterations; ++$i) {
                $timeMemory = self::getFunctionMemory(fn() => $benchmarkMethod->reflectionMethod->invoke($this));
                $this->benchmarkResults->attach($benchmarkMethod->description, $timeMemory);
            }
        }

        return $this->benchmarkResults;
    }

    final protected function buildContainer(): void
    {
        $this->container = $this->containerBuilder->build();
    }
}
