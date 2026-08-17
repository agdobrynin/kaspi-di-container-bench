<?php

use Kaspi\Benchmark\Core\BenchmarkResults;
use Kaspi\Benchmark\DiContainerFindTaggedDefinitions;
use Kaspi\Benchmark\DiContainerGet;

return static function (string $ver): Generator {
    yield new DiContainerFindTaggedDefinitions(
        new BenchmarkResults($ver.'/find-tagged-definitions'),
    );

    yield new DiContainerGet(
        new BenchmarkResults($ver.'/get'),
    );
};
