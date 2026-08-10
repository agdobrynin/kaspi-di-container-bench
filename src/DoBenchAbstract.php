<?php

declare(strict_types=1);

namespace App;

use Kaspi\DiContainer\Interfaces\DiContainerBuilderInterface;
use Kaspi\DiContainer\Interfaces\DiContainerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use function array_unshift;
use function hrtime;
use function memory_get_peak_usage;
use function memory_get_usage;
use function str_pad;
use function substr;
use function usort;
use const STR_PAD_BOTH;

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
        // Cheks available benchmark methods
        $methods = (new ReflectionClass($this))
            ->getMethods(ReflectionMethod::IS_PUBLIC)
        ;

        foreach ($methods as $method) {
            if (!$method->isStatic()) {
                $attribute = $method->getAttributes(Benchmark::class)[0] ?? null;

                if (null === $attribute) {
                    continue;
                }

                /** @var Benchmark $attributeBenchmark */
                $attributeBenchmark = $attribute->newInstance();

                $description = '' !== $attributeBenchmark->description
                    ? $attributeBenchmark->description
                    : self::methodToHuman($method->getName());

                $this->benchmarkMethods[] = new BenchMethod(
                    $description,
                    $method,
                    $attributeBenchmark->priority,
                    $attributeBenchmark->iterations,
                );
            }
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
        foreach ($this->benchmarkMethods as $benchmarkMethod) {
            for ($i = 0; $i < $benchmarkMethod->iterations; ++$i) {
                $timeMemory = self::getFunctionMemory(fn() => $benchmarkMethod->method->invoke($this));
                $this->benchmarkResults->attach($benchmarkMethod->description, $timeMemory);
            }
        }

        return $this->benchmarkResults;
    }

    final public function displayResults(): void
    {
        print <<< TABLEHEAD

+-----+-------+---------------------------------------------------+-----------------------+----------------+
|     |       |                                                   |     Memory usage      |                |
| No. | Iter. | Benchmark description                             +-----------+-----------+ Time execution |
|     |       |                                                   |    Net    |   Peak    |                |
+-----+-------+---------------------------------------------------+-----------+-----------+----------------|

TABLEHEAD;
        $avgResults = $this->benchmarkResults->getAvgResults();
        $n = 1;

        foreach ($avgResults as $benchmarkDescription => $timeExecuteMemoryUseAvg) {
            $no = str_pad($n . '', 5, ' ', STR_PAD_BOTH);
            $iter = str_pad($timeExecuteMemoryUseAvg->iterations . '', 7, ' ', STR_PAD_BOTH);

            $description_cut = \strlen($benchmarkDescription) > 50 ?
                substr($benchmarkDescription, 0, 47) . '...'
                : $benchmarkDescription;
            $prepare_description = ' ' . str_pad($description_cut, 50);

            $net = str_pad(Formatter::formatBytes($timeExecuteMemoryUseAvg->memoryUsage, 4), 11, ' ', STR_PAD_BOTH);
            $peak = str_pad(Formatter::formatBytes($timeExecuteMemoryUseAvg->memoryPeak, 4), 11, ' ', STR_PAD_BOTH);
            $time = str_pad(Formatter::formatTimeExecute($timeExecuteMemoryUseAvg->hrTime, 4), 16, ' ', STR_PAD_BOTH);
            print <<< ROW
|$no|$iter|$prepare_description|$net|$peak|$time|
+-----+-----------------------------------------------------------+-----------+-----------+----------------+

ROW;
            $n++;
        }

        print "\n";
    }

    final protected function buildContainer(): void
    {
        $this->container = $this->containerBuilder->build();
    }
}
