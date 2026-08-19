<?php
declare(strict_types=1);

use Kaspi\Benchmark\Config\Configuration;
use Kaspi\Benchmark\Core\BenchmarkPrinter;
use Kaspi\Benchmark\Core\BenchmarkResultsFile;

require_once __DIR__ . '/vendor/autoload.php';

$file = new BenchmarkResultsFile(Configuration::JsonFileResult->getValue());
$printer = new BenchmarkPrinter();

foreach ($file->read() as $benchmarkResult) {
    $printer->attach($benchmarkResult);
}

$printer->printCompareVersions();
