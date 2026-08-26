<?php
declare(strict_types=1);

use Kaspi\Benchmark\BenchmarkPrinter;
use Kaspi\Benchmark\BenchmarkResultsFile;
use Kaspi\Benchmark\Config\Configuration;

require_once __DIR__ . '/vendor/autoload.php';

$file = new BenchmarkResultsFile(Configuration::JsonFileResult->getValue());
$printer = new BenchmarkPrinter();

foreach ($file->read() as $benchmarkResult) {
    $printer->attach($benchmarkResult);
}

$printer->printCompareVersions();
