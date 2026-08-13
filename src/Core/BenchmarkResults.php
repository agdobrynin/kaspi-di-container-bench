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
     * @var array{non-empty-string, TimeExecuteMemoryUseStatistic}
     */
    private array $timeExecuteMemoryUseStatisticItems;

    public function __construct(public readonly string $doBenchName) {}

    public function attach(string $benchmarkDescription, TimeExecuteMemoryUseIteration $result): void
    {
        $this->results[$benchmarkDescription][] = $result;
        unset($this->timeExecuteMemoryUseStatisticItems);
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
     * @return array<non-empty-string, TimeExecuteMemoryUseStatistic>
     */
    public function getTimeExecuteMemoryUseStatisticItems(): array
    {
        if (isset($this->timeExecuteMemoryUseStatisticItems)) {
            return $this->timeExecuteMemoryUseStatisticItems;
        }

        $this->timeExecuteMemoryUseStatisticItems = [];

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

            $this->timeExecuteMemoryUseStatisticItems[$benchmarkDescription] = new TimeExecuteMemoryUseStatistic(
                $srcMemoryAllocated,
                $srcMemoryPeak,
                $srcTime,
                $iterations,
            );
        }

        return $this->timeExecuteMemoryUseStatisticItems;
    }

    public function reset(): void
    {
        unset($this->results, $this->timeExecuteMemoryUseStatisticItems);
    }
}
