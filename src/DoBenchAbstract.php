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
use function str_pad;
use function str_starts_with;
use function substr;
use const STR_PAD_BOTH;

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
            $timeMemory = self::getFunctionMemory(function () use ($containerOrInitializer) {
                $this->container = ($containerOrInitializer)();
            });
            print "Memory usage: Net: " . $timeMemory->formatMemoryNet() . " bytes, Peak : " . $timeMemory->formatMemoryPeak() . " bytes \n";
            print  "Time execution: " . $timeMemory->formatTimeExecute() . "\n";
        } else {
            $this->container = $containerOrInitializer;
        }
    }

    protected static function getFunctionMemory(callable $callback): TimeExecuteMemoryUse
    {
        $startMemory = memory_get_usage();
        $startPeak = memory_get_peak_usage();
        $hrStart = hrtime(true);

        // Execute the target function
        $callback();

        return new TimeExecuteMemoryUse(
            $startMemory,
            memory_get_usage(),
            $startPeak,
            memory_get_peak_usage(),
            $hrStart,
            hrtime(true),
        );
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
        print <<< TABLEHEAD

+-----+---------------------------------------------------+-----------------------+----------------+
|     |                                                   |     Memory usage      |                |
| No. | Benchmark description                             +-----------+-----------+ Time execution |
|     |                                                   |    Net    |   Peak    |                |
+-----+---------------------------------------------------+-----------+-----------+----------------|

TABLEHEAD;
        $n = 1;
        foreach ($this->benchmarkMethods as $description => $method) {
            $timeMemory = self::getFunctionMemory(fn() => $method->invoke($this));

            $no = str_pad($n . '', 5, ' ', STR_PAD_BOTH);
            $net = str_pad($timeMemory->formatMemoryNet(4), 11, ' ', STR_PAD_BOTH);
            $peak = str_pad($timeMemory->formatMemoryPeak(4), 11, ' ', STR_PAD_BOTH);
            $time = str_pad($timeMemory->formatTimeExecute(), 16, ' ', STR_PAD_BOTH);
            $description_cut = \strlen($description) > 50 ?
                substr($description, 0, 47) . '...'
                : $description;
            $prepare_description = ' ' . str_pad($description_cut, 50);
            print <<< ROW
|$no|$prepare_description|$net|$peak|$time|
+-----+---------------------------------------------------+-----------+-----------+----------------+

ROW;

            ++$n;
        }
    }
}
