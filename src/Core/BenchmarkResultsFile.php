<?php

declare(strict_types=1);

namespace Kaspi\Benchmark\Core;

use Generator;
use JsonException;
use function array_map;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function json_decode;
use function json_encode;
use const JSON_BIGINT_AS_STRING;
use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

final class BenchmarkResultsFile
{
    /**
     * The array key is the name of a group of benchmark results.
     *
     * @var array<non-empty-string, BenchmarkResults>
     */
    private array $attachedBenchmarkResults;

    public function __construct(private readonly string $outputFile) {}

    public function attach(BenchmarkResults $benchmarkResults): self
    {
        $this->attachedBenchmarkResults[$benchmarkResults->groupName] = $benchmarkResults;

        return $this;
    }

    public function reset(): void
    {
        unset($this->attachedBenchmarkResults);
    }

    /**
     * @throws JsonException
     */
    public function save(): self
    {
        if (!isset($this->attachedBenchmarkResults)) {
            return $this;
        }

        $results = [];
        $this->getArrayFromJson($results);

        foreach ($this->attachedBenchmarkResults as $benchmarkResults) {
            foreach ($benchmarkResults->getResults() as $benchmarkDescription => $timeExecuteMemoryUseIterations) {
                $results[$benchmarkResults->groupName][$benchmarkDescription] = array_map(
                    static fn (TimeExecuteMemoryUsageIteration $i) => (array) $i,
                    $timeExecuteMemoryUseIterations
                );
            }
        }

        $json = json_encode($results, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING);

        file_put_contents($this->outputFile, $json);

        return $this;
    }

    /**
     * @return Generator<BenchmarkResults>
     */
    public function read(): Generator
    {
        $results = [];
        $this->getArrayFromJson($results);

        foreach ($results as $groupName => $benchmarkResults) {
            $benchmarkResults = new BenchmarkResults($groupName);

            foreach ($benchmarkResults as $benchmarkDescription => $timeExecuteMemoryUseIterations) {
                $benchmarkResults->attachResults(
                    $benchmarkDescription,
                    array_map(static fn (array $i) => new TimeExecuteMemoryUsageIteration(...$i), $timeExecuteMemoryUseIterations)
                );
            }

            yield $benchmarkResults;
        }
    }

    private function getArrayFromJson(array &$results): void
    {
        if (file_exists($this->outputFile)) {
            $content = file_get_contents($this->outputFile);

            try {
                $results = json_decode($content, true, flags: JSON_THROW_ON_ERROR | JSON_BIGINT_AS_STRING);
            } catch (JsonException) {
                $results = [];
            }
        }
    }
}
