<?php
declare(strict_types=1);

namespace ContainerV45;

use App\DoBench;
use Kaspi\DiContainer\DiContainerBuilder;

require_once __DIR__ . '/../vendor/autoload.php';

(new DoBench(
    'v4.5.0',
    static fn () => (new DiContainerBuilder())
        ->import('App\\', __DIR__ . '/../../src/Services')
        ->build(),
))
    ->doBenchmark();