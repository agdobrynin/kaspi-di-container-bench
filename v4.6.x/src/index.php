<?php
declare(strict_types=1);

namespace ContainerV46;

use Kaspi\Benchmark\BenchmarkResultsFile;
use Kaspi\Benchmark\Config\Configuration;

require_once __DIR__ . '/../vendor/autoload.php';

$benchResultsFile = new BenchmarkResultsFile(Configuration::JsonFileResult->getValue());

$runner = require __DIR__.'/../../src/benchmark_runner.php';

foreach ($runner->doBenchmarks() as $result) {
    $benchResultsFile->attach($result);
}

$benchResultsFile->save();

