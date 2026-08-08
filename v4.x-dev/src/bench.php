<?php
declare(strict_types=1);

namespace ContainerV46Dev;

use App\DoBench;
use App\ResultFile;
use Kaspi\DiContainer\DiContainerBuilder;
use function dirname;

require_once __DIR__ . '/../vendor/autoload.php';

$configFile = dirname(__DIR__, 2) . '/src/Services/_di_config.php';

(new DoBench(
    (new DiContainerBuilder())
        ->load($configFile)
))
    ->doBenchmark(
        new ResultFile(
            'v4.x-dev',
            dirname(__DIR__, 2) . '/src/var',
        )
    );
