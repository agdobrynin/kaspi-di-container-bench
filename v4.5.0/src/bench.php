<?php
declare(strict_types=1);

namespace ContainerV45;

use App\DoBench;
use App\ResultFile;
use Kaspi\DiContainer\DiContainerBuilder;
use function dirname;

require_once __DIR__ . '/../vendor/autoload.php';

(new DoBench(
    (new DiContainerBuilder())
        ->import('App\\', dirname(__DIR__, 2) . '/src/Services'),
    'Build container with `import()` definitions',
))
    ->doBenchmark(
        new ResultFile(
            'v4.5.0',
            dirname(__DIR__, 2) . '/src/var',
        )
    );