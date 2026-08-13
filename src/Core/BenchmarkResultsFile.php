<?php

declare(strict_types=1);

namespace Kaspi\Benchmark\Core;

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
    public function __construct(
        private readonly BenchmarkResults $benchmarkResults,
        private readonly string $outputFile,
    ) {}

    /**
     * @throws \JsonException
     */
    public function save(): self
    {
        $results = [];

        if (file_exists($this->outputFile)) {
            $content = file_get_contents($this->outputFile);
            try {
                $results = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
            } catch (JsonException) {
                $results = [];
            }
        }

        $benchmarks = [];

        foreach ($this->benchmarkResults->getResults() as $description => $timeMemoryUseIterations) {
            $benchmarks[$description] = (array) $timeMemoryUseIterations;
        }

        $results[$this->benchmarkResults->doBenchName]['benchmarks'] = $benchmarks;

        $json = json_encode($results, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING);

        file_put_contents($this->outputFile, $json);

        return $this;
    }

    public function read(): self
    {
        if (!file_exists($this->outputFile)) {
            $this->benchmarkResults->reset();
        }

        $content = file_get_contents($this->outputFile);

        try {
            $results = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            $this->benchmarkResults->reset();

            return $this;
        }

        if (!isset($results[$this->benchmarkResults->doBenchName]['benchmarks'])) {
            $this->benchmarkResults->reset();

            return $this;
        }

        foreach ($results[$this->benchmarkResults->doBenchName]['benchmarks'] as $description => $timeMemoryUseIterations) {
            foreach ($timeMemoryUseIterations as $timeMemoryUseIteration) {
                $this->benchmarkResults->attach($description, new TimeExecuteMemoryUseIteration(...$timeMemoryUseIteration));
            }
        }

        return $this;
    }
}
