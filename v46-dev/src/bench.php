<?php
declare(strict_types=1);

namespace ContainerV46Dev;

use App\DoBenchFindTaggedDefinitions;
use Kaspi\DiContainer\DiContainerBuilder;

require_once __DIR__ . '/../vendor/autoload.php';


$container = (new DiContainerBuilder())
    ->load(__DIR__ . '/../../src/_di_config.php')
    ->build();

DoBenchFindTaggedDefinitions::doBench('v46-dev', $container);
