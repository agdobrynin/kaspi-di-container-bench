<?php
declare(strict_types=1);

namespace ContainerV46Dev;

use App\DoBench;
use Kaspi\DiContainer\DiContainerBuilder;
use function realpath;

require_once __DIR__ . '/../vendor/autoload.php';

$configFile = dirname(__DIR__, 2) . '/src/Services/_di_config.php';

(new DoBench(
    '[v4.x-dev] Init container.',
    static fn() => (new DiContainerBuilder())
        ->load($configFile)
        ->build()
))
    ->doBenchmark();
