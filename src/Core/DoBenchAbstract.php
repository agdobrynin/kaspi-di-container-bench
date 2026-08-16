<?php

declare(strict_types=1);

namespace Kaspi\Benchmark\Core;

use Generator;
use InvalidArgumentException;
use Kaspi\Benchmark\Core\Attributes\AfterMethod;
use Kaspi\Benchmark\Core\Attributes\BeforeMethod;
use Kaspi\Benchmark\Core\Attributes\Benchmark;
use Kaspi\Benchmark\Core\Attributes\Iterations;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use function gc_collect_cycles;
use function gc_enable;
use function hrtime;
use function is_int;
use function is_string;
use function memory_get_peak_usage;
use function memory_get_usage;
use function sprintf;
use function usort;
use function var_export;

abstract class DoBenchAbstract
{
    /**
     * @var BenchMethod[]
     */
    protected readonly array $benchmarkMethods;

    protected readonly ReflectionClass $reflectionClass;

    /**
     * @var array<non-empty-string, ReflectionMethod>
     */
    protected readonly array $reflectionMethods;

    protected readonly int $iterationsOnClass;

    /**
     * @var list<ReflectionMethod>
     */
    protected readonly array $beforeMethodOnClass;

    /**
     * @var list<ReflectionMethod>
     */
    protected readonly array $afterMethodOnClass;

    final public function __construct(
        protected readonly BenchmarkResults $benchmarkResults,
        protected readonly bool $showProgressBar = true,
    ) {
        gc_enable();

        $this->reflectionClass = new ReflectionClass($this);

        // Find available methods
        $reflectionMethods = [];

        foreach ($this->reflectionClass->getMethods()  as $reflectionMethod) {
            $reflectionMethods[$reflectionMethod->getName()] = $reflectionMethod;
        }

        $this->reflectionMethods = $reflectionMethods;

        /** @var list<ReflectionAttribute<Iterations>> $iterationsOnClassAttributes */
        $iterationsOnClassAttributes = $this->reflectionClass->getAttributes(Iterations::class);

        $this->iterationsOnClass = isset($iterationsOnClassAttributes[0])
            ? $iterationsOnClassAttributes[0]->newInstance()->iterations
            : 1;

        /** @var list<ReflectionAttribute<BeforeMethod>> $beforeMethodOnClassAttributes */
        $beforeMethodOnClassAttributes = $this->reflectionClass->getAttributes(BeforeMethod::class);

        if (isset($beforeMethodOnClassAttributes[0])) {
            $beforeMethodOnClassAttribute = $beforeMethodOnClassAttributes[0]->newInstance();

            $this->beforeMethodOnClass = [...$this->checkAvailableMethod(
                (array) $beforeMethodOnClassAttribute->beforeMethod,
                $beforeMethodOnClassAttribute::class,
                'beforeMethod',
                $this->reflectionClass,
            )];
        } else {
            $this->beforeMethodOnClass = [];
        }

        /** @var list<ReflectionAttribute<AfterMethod>> $afterMethodOnClassAttributes */
        $afterMethodOnClassAttributes = $this->reflectionClass->getAttributes(AfterMethod::class);

        if (isset($afterMethodOnClassAttributes[0])) {
            $afterMethodOnClassAttribute = $afterMethodOnClassAttributes[0]->newInstance();

            $this->afterMethodOnClass = [...$this->checkAvailableMethod(
                (array) $afterMethodOnClassAttribute->afterMethod,
                $afterMethodOnClassAttribute::class,
                'afterMethod',
                $this->reflectionClass,
            )];
        } else {
            $this->afterMethodOnClass = [];
        }

        foreach ($this->reflectionMethods as $methodName => $reflectionMethod) {
            // Benchmark method must be declared with modifier public and non-static
            if (!$reflectionMethod->isPublic() || $reflectionMethod->isStatic()) {
                continue;
            }

            $attribute = $reflectionMethod->getAttributes(Benchmark::class)[0] ?? null;

            if (null === $attribute) {
                continue;
            }

            /** @var Benchmark $attributeBenchmark */
            $attributeBenchmark = $attribute->newInstance();
            $description = '' !== $attributeBenchmark->description
                ? $attributeBenchmark->description
                : self::methodToHuman($methodName);

            $iterations = is_int($attributeBenchmark->iterations)
                ? $attributeBenchmark->iterations
                : $this->iterationsOnClass;

            $beforeMethods = [] !== $attributeBenchmark->beforeMethod
                ? [...$this->checkAvailableMethod(
                    (array)$attributeBenchmark->beforeMethod,
                    $attributeBenchmark::class,
                    'beforeMethod',
                    $reflectionMethod)
                ]
                : $this->beforeMethodOnClass;

            $afterMethods = [] !== $attributeBenchmark->afterMethod
                ? [...$this->checkAvailableMethod(
                    (array)$attributeBenchmark->afterMethod,
                    $attributeBenchmark::class,
                    'afterMethod',
                    $reflectionMethod)
                ]
                : $this->afterMethodOnClass;

            $benchmarkMethods[] = new BenchMethod(
                $description,
                $reflectionMethod,
                $attributeBenchmark->priority,
                $iterations,
                $beforeMethods,
                $afterMethods,
            );
        }

        usort($benchmarkMethods, static function (BenchMethod $a, BenchMethod $b) {
            return $b->priority <=> $a->priority;
        });

        $this->benchmarkMethods = $benchmarkMethods;
    }

    final protected static function runBenchmark(callable $callback): TimeExecuteMemoryUsageIteration
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

        return new TimeExecuteMemoryUsageIteration(
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
            foreach ($benchmarkMethod->beforeReflectionMethod as $beforeMethod) {
                $beforeMethod->invoke($this);
            }

            if ($this->showProgressBar) {
                print "\n";
                $benchmarkTitle = sprintf('[%s] %s', $this->benchmarkResults->groupName, $benchmarkMethod->description);
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

            foreach ($benchmarkMethod->afterReflectionMethod as $afterMethod) {
                $afterMethod->invoke($this);
            }
        }

        if ($this->showProgressBar) {
            print "\n";
        }

        return $this->benchmarkResults;
    }

    /**
     * @param non-empty-list<non-empty-string> $methods
     *
     * @return Generator<ReflectionMethod>
     */
    final protected function checkAvailableMethod(array $methods, string $classAttribute, string $parameterName, ReflectionClass|ReflectionMethod $on): Generator
    {
        foreach ($methods as $method) {
            if (!isset($this->reflectionMethods[$method])) {
                $onName = $on instanceof ReflectionClass
                    ? $on->getName().'::class'
                    : $on->getDeclaringClass()->getName().'::'.$on->getName().'()';
                throw new InvalidArgumentException(
                    sprintf('The attribute `%s` failed validation for the `%s`. The value of the `$%s` parameter must refer to a public class method. Got value %s.', $classAttribute, $onName, $parameterName, var_export($method, true))
                );
            }

            yield $this->reflectionMethods[$method];
        }
    }
}
