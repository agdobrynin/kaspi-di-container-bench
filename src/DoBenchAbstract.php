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
use function printf;
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

    final public function __construct(protected readonly DiContainerBuilderInterface $containerBuilder) {
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

                $this->benchmarkMethods[] = new BenchMethod($description, $method, $attributeBenchmark->priority);
            }
        }

        usort($this->benchmarkMethods, static function (BenchMethod $a, BenchMethod $b) {
            return $b->priority <=> $a->priority;
        });

        array_unshift(
            $this->benchmarkMethods,
            new BenchMethod(
                'Build container',
                new ReflectionMethod($this, 'buildContainer'),
            )
        );
    }

    final protected static function getFunctionMemory(callable $callback): TimeExecuteMemoryUse
    {
        $startMemory = memory_get_usage();
        $startPeak = memory_get_peak_usage();
        $hrStart = hrtime(true);

        // Execute the target function
        $callback();

        $endMemory = memory_get_usage();
        $endPeak = memory_get_peak_usage();
        $hrEnd = hrtime(true);

        return new TimeExecuteMemoryUse(
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
    final public function doBenchmark(?ResultFile $resultFile = null): void
    {
        print <<< TABLEHEAD

+-----+---------------------------------------------------+-----------------------+----------------+
|     |                                                   |     Memory usage      |                |
| No. | Benchmark description                             +-----------+-----------+ Time execution |
|     |                                                   |    Net    |   Peak    |                |
+-----+---------------------------------------------------+-----------+-----------+----------------|

TABLEHEAD;
        $n = 1;
        foreach ($this->benchmarkMethods as $benchmarkMethod) {
            $timeMemory = self::getFunctionMemory(fn() => $benchmarkMethod->method->invoke($this));

            $no = str_pad($n . '', 5, ' ', STR_PAD_BOTH);
            $net = str_pad($timeMemory->formatMemoryNet(4), 11, ' ', STR_PAD_BOTH);
            $peak = str_pad($timeMemory->formatMemoryPeak(4), 11, ' ', STR_PAD_BOTH);
            $time = str_pad($timeMemory->formatTimeExecute(), 16, ' ', STR_PAD_BOTH);
            $description_cut = \strlen($benchmarkMethod->description) > 50 ?
                substr($benchmarkMethod->description, 0, 47) . '...'
                : $benchmarkMethod->description;
            $prepare_description = ' ' . str_pad($description_cut, 50);
            print <<< ROW
|$no|$prepare_description|$net|$peak|$time|
+-----+---------------------------------------------------+-----------+-----------+----------------+

ROW;

            ++$n;

            $resultFile?->attachTo($benchmarkMethod->description, $timeMemory);
        }

        print "\n";
        $resultFile?->save();
    }

    final protected function buildContainer(): void
    {
        $this->container = $this->containerBuilder->build();
    }
}
