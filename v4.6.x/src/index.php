<?php
declare(strict_types=1);

namespace ContainerV46;

use Kaspi\Benchmark\Config\Configuration;
use Kaspi\Benchmark\Core\BenchmarkResults;
use Kaspi\Benchmark\Core\BenchmarkResultsFile;
use Kaspi\Benchmark\BenchDiContainer;


require_once __DIR__ . '/../vendor/autoload.php';

$results = (new BenchDiContainer(
    new BenchmarkResults('v4.6.x')
))
    ->doBenchmarks();

(new BenchmarkResultsFile(Configuration::JsonFileResult->getValue()))
    ->attach($results)
    ->save();
