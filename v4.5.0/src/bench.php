<?php
declare(strict_types=1);

namespace ContainerV45;

use App\BenchmarkPrinter;
use App\BenchmarkResultsToFile;
use App\DoBench;
use App\BenchmarkResults;
use Kaspi\DiContainer\DiContainerBuilder;
use function dirname;

require_once __DIR__ . '/../vendor/autoload.php';

$containerBuilder = (new DiContainerBuilder())
    ->import('Fixtures\\', dirname(__DIR__, 2) . '/Fixtures');
$benchmarkResults = new BenchmarkResults('v4.5.0');

$results = (new DoBench($containerBuilder, $benchmarkResults))
    ->doBenchmark();

(new BenchmarkPrinter($results))
    ->print();

(new BenchmarkResultsToFile(
    $results,
    dirname(__DIR__, 2) . '/var/results.json',
))
    ->save();