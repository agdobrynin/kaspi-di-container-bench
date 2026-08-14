<?php
declare(strict_types=1);

namespace ContainerV45;

use Kaspi\Benchmark\Configuration;
use Kaspi\Benchmark\Core\BenchmarkResults;
use Kaspi\Benchmark\Core\BenchmarkResultsFile;
use Kaspi\Benchmark\BenchFindTaggedDefinitions;


require_once __DIR__ . '/../vendor/autoload.php';

$results = (new BenchFindTaggedDefinitions(
    new BenchmarkResults('v4.5.x')
))
    ->doBenchmarks();

(new BenchmarkResultsFile(Configuration::JsonFileResult->getValue()))
    ->attach($results)
    ->save();
