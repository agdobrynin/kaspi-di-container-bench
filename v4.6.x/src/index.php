<?php
declare(strict_types=1);

namespace ContainerV46;

use Kaspi\Benchmark\Core\BenchmarkPrinter;
use Kaspi\Benchmark\Core\BenchmarkResults;
use Kaspi\Benchmark\Core\BenchmarkResultsFile;
use Kaspi\Benchmark\BenchFindTaggedDefinitions;

use function dirname;

require_once __DIR__ . '/../vendor/autoload.php';

$results = (new BenchFindTaggedDefinitions(new BenchmarkResults('v4.6.x')))
    ->doBenchmarks();

(new BenchmarkPrinter($results))
    ->print();

(new BenchmarkResultsFile(
    $results,
    dirname(__DIR__, 2) . '/var/results.json',
))
    ->save();
