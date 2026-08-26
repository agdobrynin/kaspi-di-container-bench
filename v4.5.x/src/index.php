<?php
declare(strict_types=1);

namespace ContainerV45;

use Kaspi\Benchmark\BenchmarkResultsFile;
use Kaspi\Benchmark\BenchmarkRunner;
use Kaspi\Benchmark\Config\Configuration;

require_once __DIR__ . '/../vendor/autoload.php';

$benchResultsFile = new BenchmarkResultsFile(Configuration::JsonFileResult->getValue());

/** @var callable(string $ver): list<BenchmarkRunner> $benches */
$benches = require __DIR__.'/../../src/index.php';

foreach ($benches() as $bench) {
    $benchResultsFile->attach($bench->doBenchmarks());
}

$benchResultsFile->save();
