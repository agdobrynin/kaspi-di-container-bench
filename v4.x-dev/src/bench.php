<?php
declare(strict_types=1);

namespace ContainerV46Dev;

use App\DoBench;
use Kaspi\DiContainer\DiContainerBuilder;

require_once __DIR__ . '/../vendor/autoload.php';


(new DoBench(
    'v4.x-dev',
    static fn() => (new DiContainerBuilder())
        ->import('App\\', __DIR__ . '/../../src/Services')
        ->build()
))
    ->doBenchmark();
