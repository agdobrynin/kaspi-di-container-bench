<?php

use App\DiContainerFindTaggedDefinitions;
use App\DiContainerGet;
use App\DiContainerHas;
use Composer\InstalledVersions;
use Kaspi\Benchmark\BenchmarkResults;
use Kaspi\Benchmark\BenchmarkRunner;

return static function (): Generator {
    $version = InstalledVersions::getPrettyVersion('kaspi/di-container');

    yield new BenchmarkRunner(
        new BenchmarkResults($version,'Find tagged definitions'),
        new DiContainerFindTaggedDefinitions,
    );

    yield new BenchmarkRunner(
        new BenchmarkResults($version, 'Has services'),
        new DiContainerHas,
    );

    yield new BenchmarkRunner(
        new BenchmarkResults($version, 'Get services'),
        new DiContainerGet,
    );
};
