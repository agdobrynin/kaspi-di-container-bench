<?php

declare(strict_types=1);

namespace Kaspi\Benchmark\Core;

use function count;

final class BenchmarkResults
{
    /**
     * @var array{non-empty-string, list<TimeExecuteMemoryUsageIteration>}
     */
    private array $results = [];

    /**
     * @var array{non-empty-string, TimeExecuteMemoryUsingSum}
     */
    private array $timeExecuteMemoryUsingSumItems;

    /**
     * @param non-empty-string $groupName
     */
    public function __construct(public readonly string $groupName) {}

    /**
     * @param non-empty-string $benchmarkDescription
     */
    public function attach(string $benchmarkDescription, TimeExecuteMemoryUsageIteration $result): void
    {
        $this->results[$benchmarkDescription][] = $result;
        unset($this->timeExecuteMemoryUsingSumItems);
    }

    /**
     * @param non-empty-string                      $benchmarkDescription
     * @param list<TimeExecuteMemoryUsageIteration> $results
     */
    public function attachResults(string $benchmarkDescription, array $results): void
    {
        $this->results[$benchmarkDescription] = $results;
        unset($this->timeExecuteMemoryUsingSumItems);
    }

    /**
     * A key of array benchmark description.
     *
     * @return array{non-empty-string, list<TimeExecuteMemoryUsageIteration>}
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
         * @var list<TimeExecuteMemoryUsageIteration> $benchmarkResults
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
