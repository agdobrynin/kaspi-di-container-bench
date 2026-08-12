<?php

declare(strict_types=1);

namespace Kaspi\Benchmark\Core;

use function count;

final class BenchmarkResults
{
    /**
     * @var array{non-empty-string, list<TimeExecuteMemoryUseIteration>}
     */
    private array $results = [];

    /**
     * @var array{non-empty-string, TimeExecuteMemoryUseAverage}
     */
    private array $avgResults;

    public function __construct(public readonly string $doBenchName) {}

    public function attach(string $benchmarkDescription, TimeExecuteMemoryUseIteration $result): void
    {
        $this->results[$benchmarkDescription][] = $result;
        unset($this->avgResults);
    }

    /**
     * A key of array benchmark description.
     *
     * @return TimeExecuteMemoryUseIteration[]
     */
    public function getResults(): array
    {
        return $this->results;
    }

    /**
     * A key of array benchmark description.
     *
     * @return array<non-empty-string, TimeExecuteMemoryUseAverage>
     */
    public function getAvgResults(): array
    {
        if (isset($this->avgResults)) {
            return $this->avgResults;
        }

        $this->avgResults = [];

        /**
         * @var non-empty-string $benchmarkDescription
         * @var list<TimeExecuteMemoryUseIteration> $benchmarkResults
         */
        foreach ($this->results as $benchmarkDescription => $benchmarkResults) {
            $srcMemoryAllocated = $srcMemoryPeak = $srcTime = 0;
            $iterations = count($benchmarkResults);

            foreach ($benchmarkResults as $benchmarkResult) {
                if ($srcMemoryAllocated < $benchmarkResult->memoryUsage()) {
                    $srcMemoryAllocated = $benchmarkResult->memoryUsage();
                }

                if ($srcMemoryPeak < $benchmarkResult->memoryPeakUsage()) {
                    $srcMemoryPeak = $benchmarkResult->memoryPeakUsage();
                }

                $srcTime += $benchmarkResult->HrTime();
            }

            $this->avgResults[$benchmarkDescription] = new TimeExecuteMemoryUseAverage(
                $srcMemoryAllocated,
                $srcMemoryPeak,
                ($srcTime / $iterations),
                $iterations,
            );
        }

        return $this->avgResults;
    }

    public function reset(): void
    {
        unset($this->results, $this->avgResults);
    }
}
