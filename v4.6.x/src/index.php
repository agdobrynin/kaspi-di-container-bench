<?php
declare(strict_types=1);

namespace ContainerV46;

use Kaspi\Benchmark\Config\Configuration;
use Kaspi\Benchmark\Core\BenchmarkResultsFile;
use Kaspi\Benchmark\Core\DoBenchAbstract;

require_once __DIR__ . '/../vendor/autoload.php';

$benchResultsFile = new BenchmarkResultsFile(Configuration::JsonFileResult->getValue());

/** @var callable(string $ver): list<DoBenchAbstract> $benches */
$benches = require __DIR__.'/../../src/index.php';

foreach ($benches('v4.6.x') as $bench) {
    $benchResultsFile->attach($bench->doBenchmarks());
}

$benchResultsFile->save();
