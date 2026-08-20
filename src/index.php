<?php

use Composer\InstalledVersions;
use Kaspi\Benchmark\Core\BenchmarkResults;
use Kaspi\Benchmark\DiContainerFindTaggedDefinitions;
use Kaspi\Benchmark\DiContainerGet;
use Kaspi\Benchmark\DiContainerHas;

return static function (): Generator {
    $version = InstalledVersions::getPrettyVersion('kaspi/di-container');

    yield new DiContainerFindTaggedDefinitions(
        new BenchmarkResults($version,'Find tagged definitions'),
    );

    yield new DiContainerGet(
        new BenchmarkResults($version, 'Get services'),
    );

    yield new DiContainerHas(
        new BenchmarkResults($version, 'Has services'),
    );
};
