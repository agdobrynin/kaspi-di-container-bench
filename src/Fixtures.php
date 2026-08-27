<?php

declare(strict_types=1);

namespace App;

use Fixtures\Services\Interfaces\ServiceInterface;
use Fixtures\Services\Service300;
use Fixtures\Services\Service6;
use Kaspi\Benchmark\Config\Configuration;
use function Kaspi\DiContainer\diAutowire;
use function Kaspi\DiContainer\diGet;

final class Fixtures
{
    /**
     * @return array<non-empty-string, true>
     */
    public static function randomExistIds(): array
    {
        $serviceWithNamespace = Configuration::ServicesNamespace->getValue().'\\'.Configuration::ServicesNamePrefix->getValue();

        return [
            $serviceWithNamespace . '500' => true,
            $serviceWithNamespace . '1' => true,
            $serviceWithNamespace . '950' => true,
            $serviceWithNamespace . '4' => true,
            $serviceWithNamespace . '1000' => true,
            $serviceWithNamespace . '263' => true,
            $serviceWithNamespace . '790' => true,
            $serviceWithNamespace . '2' => true,
            $serviceWithNamespace . '420' => true,
            $serviceWithNamespace . '999' => true,
        ];
    }

    public static function configuredDefinitions(): array
    {
        return [
            ServiceInterface::class => diAutowire(Service6::class),
            'alias_of_service_300' => diGet(Service300::class),
        ];
    }

    /**
     * @return array<non-empty-string, false>
     */
    public static function randomNoneExistIds(): array
    {
        $serviceWithNamespace = Configuration::ServicesNamespace->getValue().'\\'.Configuration::ServicesNamePrefix->getValue();

        return [
            $serviceWithNamespace . '__500' => false,
            $serviceWithNamespace . '__1' => false,
            $serviceWithNamespace . '__950' => false,
            $serviceWithNamespace . '__4' => false,
            $serviceWithNamespace . '__1000' => false,
            $serviceWithNamespace . '__263' => false,
            $serviceWithNamespace . '__790' => false,
            $serviceWithNamespace . '__2' => false,
            $serviceWithNamespace . '__420' => false,
            $serviceWithNamespace . '__999' => false,
        ];
    }
}
