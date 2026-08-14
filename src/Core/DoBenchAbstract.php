<?php

declare(strict_types=1);

namespace Kaspi\Benchmark\Core;

use Kaspi\Benchmark\Core\Attributes\BeforeMethod;
use Kaspi\Benchmark\Core\Attributes\Benchmark;
use Kaspi\Benchmark\Core\Attributes\Iterations;
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

abstract class DoBenchAbstract
{
    /**
     * @var BenchMethod[]
     */
    protected array $benchmarkMethods = [];

    protected readonly ReflectionClass $reflectionClass;

    /**
     * @var array<non-empty-string, ReflectionMethod>
     */
    protected readonly array $reflectionMethods;

    protected readonly int $iterationsOnClass;

    protected readonly false|ReflectionMethod $beforeMethodOnClass;

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

            $this->benchmarkMethods[] = new BenchMethod(
                $description,
                $reflectionMethod,
                $attributeBenchmark->priority,
                $this->configureIterationMethod($attributeBenchmark),
                $this->configureBeforeMethod($attributeBenchmark, $reflectionMethod),
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
            if ($benchmarkMethod->beforeReflectionMethod instanceof ReflectionMethod) {
                $benchmarkMethod->beforeReflectionMethod->invoke($this);
            }

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

    final protected function checkAvailableMethod(string $method, string $classAttribute, ReflectionClass|ReflectionMethod $on): ReflectionMethod
    {
        if (!isset($this->reflectionMethods[$method])) {
            $onName = $on instanceof ReflectionClass
                ? $on->getName().'::class'
                : $on->getDeclaringClass()->getName().'::'.$on->getName().'()';
            throw new \InvalidArgumentException(
                sprintf('The attribute `%s` failed validation for the `%s`. The value of the `$beforeMethod` parameter must refer to a public class method. Got value "%s".', $classAttribute, $onName, $method)
            );
        }

        return $this->reflectionMethods[$method];
    }

    final protected function configureIterationMethod(Benchmark $attributeBenchmark): int
    {
        if (is_int($attributeBenchmark->iterations)) {
            return $attributeBenchmark->iterations;
        }

        if ($attributeBenchmark->iterations instanceof Iterations) {
            return $attributeBenchmark->iterations->iterations;
        }

        if (isset($this->iterationsOnClass)) {
            return $this->iterationsOnClass;
        }

        /** @var Iterations|null $iterationsAttributeOnClass */
        $iterationsAttributeOnClass = isset($this->reflectionClass->getAttributes(Iterations::class)[0])
            ? $this->reflectionClass->getAttributes(Iterations::class)[0]->newInstance()
            : null;

        if ($iterationsAttributeOnClass instanceof Iterations) {
            return $this->iterationsOnClass = $iterationsAttributeOnClass->iterations;
        }

        return $this->iterationsOnClass = 1;
    }

    final protected function configureBeforeMethod(Benchmark $attributeBenchmark, ReflectionMethod $reflectionMethod): false|ReflectionMethod
    {
        if (is_string($attributeBenchmark->beforeMethod)) {
            return $this->checkAvailableMethod(
                $attributeBenchmark->beforeMethod,
                $attributeBenchmark::class,
                $reflectionMethod
            );
        }

        if ($attributeBenchmark->beforeMethod instanceof BeforeMethod) {
            return $this->checkAvailableMethod(
                $attributeBenchmark->beforeMethod->beforeMethod,
                $attributeBenchmark::class,
                $reflectionMethod,
            );
        }

        if (isset($this->beforeMethodOnClass)) {
            return $this->beforeMethodOnClass;
        }

        /** @var BeforeMethod|null $beforeMethodOnClass */
        $beforeMethodOnClass = isset($this->reflectionClass->getAttributes(BeforeMethod::class)[0])
            ? $this->reflectionClass->getAttributes(BeforeMethod::class)[0]->newInstance()
            : null;

        if ($beforeMethodOnClass instanceof BeforeMethod) {
            return $this->beforeMethodOnClass = $this->checkAvailableMethod(
                $beforeMethodOnClass->beforeMethod,
                $beforeMethodOnClass::class,
                $this->reflectionClass,
            );
        }

        return $this->beforeMethodOnClass = false;
    }
}
