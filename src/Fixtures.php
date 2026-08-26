<?php

declare(strict_types=1);

namespace App;

use Fixtures\Services\Interfaces\ServiceInterface;
use Fixtures\Services\Service300;
use Fixtures\Services\Service6;
use Generator;
use Kaspi\Benchmark\Config\Configuration;
use function Kaspi\DiContainer\diAutowire;
use function Kaspi\DiContainer\diGet;

final class Fixtures
{
    public static function randomExistIds(): array
    {
        $serviceWithNamespace = Configuration::ServicesNamespace->getValue().'\\'.Configuration::ServicesNamePrefix->getValue();

        return [
            $serviceWithNamespace . '500',
            $serviceWithNamespace . '1',
            $serviceWithNamespace . '950',
            $serviceWithNamespace . '4',
            $serviceWithNamespace . '1000',
            $serviceWithNamespace . '263',
            $serviceWithNamespace . '790',
            $serviceWithNamespace . '2',
            $serviceWithNamespace . '420',
            $serviceWithNamespace . '999'
        ];
    }

    public static function configuredDefinitions(): Generator
    {
        yield ServiceInterface::class => diAutowire(Service6::class);

        yield 'alias_of_service_300' => diGet(Service300::class);
    }

    public static function randomNoneExistIds(): array
    {
        $serviceWithNamespace = Configuration::ServicesNamespace->getValue().'\\'.Configuration::ServicesNamePrefix->getValue();

        return [
            $serviceWithNamespace . '__500',
            $serviceWithNamespace . '__1',
            $serviceWithNamespace . '__950',
            $serviceWithNamespace . '__4',
            $serviceWithNamespace . '__1000',
            $serviceWithNamespace . '__263',
            $serviceWithNamespace . '__790',
            $serviceWithNamespace . '__2',
            $serviceWithNamespace . '__420',
            $serviceWithNamespace . '__999'
        ];
    }
}
