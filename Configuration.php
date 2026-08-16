<?php
declare(strict_types=1);

namespace Kaspi\Benchmark\Config;

enum Configuration
{
    case JsonFileResult;
    case MaxIndexOfService;

    case InterfacesSrc;
    case InterfacesNamespace;
    case InterfaceName;

    case ServicesSrc;
    case ServicesNamespace;
    case ServicesNamePrefix;

    public function getValue(): mixed
    {
        return match ($this) {
            self::JsonFileResult => __DIR__ . '/var/results.json',
            self::MaxIndexOfService => 1_000,

            self::InterfacesSrc => __DIR__ . '/Fixtures/Services/Interfaces',
            self::InterfacesNamespace => 'Fixtures\\Services\\Interfaces',
            self::InterfaceName => 'ServiceInterface',

            self::ServicesSrc => __DIR__ . '/Fixtures/Services',
            self::ServicesNamespace => 'Fixtures\\Services',
            self::ServicesNamePrefix => 'Service',
        };
    }
}
