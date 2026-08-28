<?php

declare(strict_types=1);

namespace App;

use App\FixturesForBuildAndGet\FooService;
use App\FixturesForBuildAndGet\ParamsRandomServicesAndTagged;
use DomainException;
use Fixtures\Services\Interfaces\ServiceInterface;
use Fixtures\Services\Service300;
use Generator;
use Kaspi\Benchmark\Attributes\Benchmark;
use Kaspi\Benchmark\Attributes\Group;
use Kaspi\Benchmark\Attributes\Iterations;
use Kaspi\Benchmark\Attributes\NumberOfTimes;
use Kaspi\Benchmark\Attributes\Parameters;
use Kaspi\Benchmark\Config\Configuration;
use Kaspi\DiContainer\DiContainer;
use Kaspi\DiContainer\DiContainerBuilder;
use Kaspi\DiContainer\DiContainerConfig;
use function Kaspi\DiContainer\diAutowire;


#[Group('Build container and get')]
#[Iterations(100)]
#[NumberOfTimes(2)]
final class DiContainerBuildAndGetService
{
    /**
     * @param callable(): DiContainer $builder
     */
    #[Benchmark('get random service')]
    #[Parameters([self::class, 'containerBuilder'])]
    public function buildAndResolveServiceAtConstructor(callable $builder): void
    {
        $container = ($builder)();
        $service = $container->get(ParamsRandomServicesAndTagged::class);

        if (!is_object($service)) {
            throw new DomainException(
                'Invalid initial service '.ParamsRandomServicesAndTagged::class
            );
        }
        if (!$service->taggedAsBarServices->valid()) {
            throw new DomainException('Not found service tagged as "tags.bar_name"');
        }

        if (!$service->taggedAsInterfaceServices->valid()) {
            throw new DomainException('Not found service tagged as interface '.ServiceInterface::class);
        }
    }

    public static function containerBuilder(): Generator
    {
        yield 'build runtime container, zero config and one alias' => [
            static fn () => (new DiContainerBuilder(
                new DiContainerConfig(
                    useZeroConfigurationDefinition: true,
                    useAttribute: true,
                )
            ))
                ->addDefinitions([
                    'alias_of_service_300' => diAutowire(Service300::class),
                    diAutowire(FooService::class),
                ])
                ->load(Configuration::DiClassesConfigFile->getValue())
                ->build()
        ];

        yield 'build runtime container, import all and one alias' => [
            static fn () => (new DiContainerBuilder(
                new DiContainerConfig(
                    useZeroConfigurationDefinition: false,
                    useAttribute: true,
                )
            ))
                ->import('App\\', __DIR__.'/FixturesForBuildAndGet')
                ->import('Fixtures\\', __DIR__.'/../Fixtures')
                ->addDefinitions([
                    'alias_of_service_300' => diAutowire(Service300::class),
                ])
                ->build()
        ];

        yield 'build and compile container and one alias' => [
            static fn () => (new DiContainerBuilder(
                new DiContainerConfig(
                    useZeroConfigurationDefinition: false,
                    useAttribute: true,
                )
            ))
                ->import('App\\', __DIR__.'/FixturesForBuildAndGet')
                ->import('Fixtures\\', __DIR__.'/../Fixtures')
                ->addDefinitions([
                    'alias_of_service_300' => diAutowire(Service300::class),
                ])
                ->compileToFile(__DIR__.'/../var', 'ContainerBuildAndGet', options: ['force_rebuild' => true])
                ->build()
        ];
    }
}
