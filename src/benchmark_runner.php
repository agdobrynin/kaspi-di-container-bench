<?php

use App\DiContainerBuildAndGetService;
use App\DiContainerFindTaggedDefinitions;
use App\DiContainerGet;
use App\DiContainerHas;
use Composer\InstalledVersions;
use Kaspi\Benchmark\BenchmarkRunner;

return new BenchmarkRunner(
    InstalledVersions::getPrettyVersion('kaspi/di-container'),
    new DiContainerFindTaggedDefinitions,
    new DiContainerHas,
    new DiContainerBuildAndGetService,
    new DiContainerGet,
);
