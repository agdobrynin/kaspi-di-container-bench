<?php

declare(strict_types=1);

namespace App;

use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function json_decode;
use function json_encode;
use const DIRECTORY_SEPARATOR;
use const JSON_BIGINT_AS_STRING;
use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

final class ResultFile
{
    private array $results = [];

    public function __construct(
        private readonly string $doBenchName,
        private readonly string $outputDirectory,
        private readonly string $outputFilename = 'results.json',
    ) {}

    public function attachTo(string $benchmarkDescription, TimeExecuteMemoryUse $result): void
    {
        $this->results[$benchmarkDescription] = (array) $result;
    }

    public function reset(): void
    {
        unset($this->results);
    }

    /**
     * @throws \JsonException
     */
    public function save(): void
    {
        $fileName = $this->outputDirectory . DIRECTORY_SEPARATOR . $this->outputFilename;
        $results = [];

        if (file_exists($fileName)) {
            $content = file_get_contents($fileName);
            $results = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
        }

        $results[$this->doBenchName] = $this->results;

        $json = json_encode($results, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING);

        file_put_contents($fileName, $json);
    }
}
