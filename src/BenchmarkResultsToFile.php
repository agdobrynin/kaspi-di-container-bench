<?php

declare(strict_types=1);

namespace App;

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

final class BenchmarkResultsToFile
{
    public function __construct(
        private readonly BenchmarkResults $benchmarkResults,
        private readonly string $outputFile,
    ) {}

    /**
     * @throws \JsonException
     */
    public function save(): void
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

        $benchmarks = array_map(static function ($iterationsResults) {
            return array_map(
                static fn(TimeExecuteMemoryUseIteration $t) => (array)$t,
                $iterationsResults
            );
        }, $this->benchmarkResults->getResults());

        $results[$this->benchmarkResults->doBenchName]['benchmarks'] = $benchmarks;

        $json = json_encode($results, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING);

        file_put_contents($this->outputFile, $json);
    }
}
