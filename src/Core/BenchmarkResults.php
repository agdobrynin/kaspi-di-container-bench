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
     * @var array{non-empty-string, TimeExecuteMemoryUsingSum}
     */
    private array $timeExecuteMemoryUsingSumItems;

    public function __construct(public readonly string $doBenchName) {}

    public function attach(string $benchmarkDescription, TimeExecuteMemoryUseIteration $result): void
    {
        $this->results[$benchmarkDescription][] = $result;
        unset($this->timeExecuteMemoryUsingSumItems);
    }

    /**
     * A key of array benchmark description.
     *
     * @return array{non-empty-string, list<TimeExecuteMemoryUseIteration>}
     */
    public function getResults(): array
    {
        return $this->results;
    }

    /**
     * A key of array benchmark description.
     *
     * @return array<non-empty-string, TimeExecuteMemoryUsingSum>
     */
    public function getTimeExecuteMemoryUsingSumItems(): array
    {
        if (isset($this->timeExecuteMemoryUsingSumItems)) {
            return $this->timeExecuteMemoryUsingSumItems;
        }

        $this->timeExecuteMemoryUsingSumItems = [];

        /**
         * @var non-empty-string $benchmarkDescription
         * @var list<TimeExecuteMemoryUseIteration> $benchmarkResults
         */
        foreach ($this->results as $benchmarkDescription => $benchmarkResults) {
            $srcMemoryAllocated = $srcMemoryPeak = $srcTime = 0;
            $iterations = count($benchmarkResults);

            foreach ($benchmarkResults as $benchmarkResult) {
                $srcMemoryAllocated += $benchmarkResult->memoryUsage();
                $srcMemoryPeak += $benchmarkResult->memoryPeakUsage();
                $srcTime += $benchmarkResult->HrTime();
            }

            $this->timeExecuteMemoryUsingSumItems[$benchmarkDescription] = new TimeExecuteMemoryUsingSum(
                $srcMemoryAllocated,
                $srcMemoryPeak,
                $srcTime,
                $iterations,
            );
        }

        return $this->timeExecuteMemoryUsingSumItems;
    }

    public function reset(): void
    {
        unset($this->results, $this->timeExecuteMemoryUsingSumItems);
    }
}
