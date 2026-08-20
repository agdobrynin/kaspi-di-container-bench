<?php

declare(strict_types=1);

namespace Kaspi\Benchmark;

use Kaspi\Benchmark\Config\Configuration;

final class Fixtures
{
    public static function randomIdsArray(): array
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
}
