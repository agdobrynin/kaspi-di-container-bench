<?php
declare(strict_types=1);

namespace ContainerV45;

use App\BenchmarkResultsToFile;
use App\DoBench;
use App\BenchmarkResults;
use Kaspi\DiContainer\DiContainerBuilder;
use function dirname;

require_once __DIR__ . '/../vendor/autoload.php';

$containerBuilder = (new DiContainerBuilder())
    ->import('App\\', dirname(__DIR__, 2) . '/src/Services');
$benchmarkResults = new BenchmarkResults('v4.5.0');

$benchmark = new DoBench($containerBuilder, $benchmarkResults);
$results = $benchmark->doBenchmark();
$benchmark->displayResults();

(new BenchmarkResultsToFile(
    $results,
    dirname(__DIR__, 2) . '/src/var/results.json',
))
    ->save();