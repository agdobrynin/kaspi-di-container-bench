<?php

declare(strict_types=1);

namespace App;

use function count;

final class BenchmarkResults
{
    /**
     * @var array{non-empty-string, TimeExecuteMemoryUseIteration[]}
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
     * @return array<non-empty-string, TimeExecuteMemoryUseIteration[]>
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

        foreach ($this->results as $benchmarkDescription => $benchmarkResults) {
            $srcNet = $srcPeak = $srcTime = 0;
            $iterations = count($benchmarkResults);

            foreach ($benchmarkResults as $benchmarkResult) {
                $srcNet += $benchmarkResult->memoryNet();
                $srcPeak += $benchmarkResult->memoryPeak();
                $srcTime += $benchmarkResult->HrTime();
            }

            $this->avgResults[$benchmarkDescription] = new TimeExecuteMemoryUseAverage(
                ($srcNet / $iterations),
                ($srcPeak / $iterations),
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
