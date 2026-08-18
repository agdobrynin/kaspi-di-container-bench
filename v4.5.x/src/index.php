<?php
declare(strict_types=1);

namespace ContainerV45;

use Kaspi\Benchmark\Config\Configuration;
use Kaspi\Benchmark\Core\BenchmarkResultsFile;
use Kaspi\Benchmark\Core\BenchmarkAbstract;

require_once __DIR__ . '/../vendor/autoload.php';

$benchResultsFile = new BenchmarkResultsFile(Configuration::JsonFileResult->getValue());

/** @var callable(string $ver): list<BenchmarkAbstract> $benches */
$benches = require __DIR__.'/../../src/index.php';

foreach ($benches() as $bench) {
    $benchResultsFile->attach($bench->doBenchmarks());
}

$benchResultsFile->save();
