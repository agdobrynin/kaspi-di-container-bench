<?php

declare(strict_types=1);

namespace App;

use DomainException;
use Generator;
use Kaspi\Benchmark\Attributes\Benchmark;
use Kaspi\Benchmark\Attributes\Group;
use Kaspi\Benchmark\Attributes\Iterations;
use Kaspi\Benchmark\Attributes\NumberOfTimes;
use Kaspi\Benchmark\Attributes\Parameters;
use Kaspi\DiContainer\DiContainer;
use Kaspi\DiContainer\DiContainerBuilder;
use Kaspi\DiContainer\DiContainerConfig;
use function array_keys;
use function sprintf;

#[Group('Has services')]
#[Iterations(100)]
#[NumberOfTimes(2)]
final class DiContainerHas
{
    #[Benchmark]
    #[Parameters([self::class, 'ContainerZeroOnRandomIds'])]
    public function hasServiceWithZeroConfigTrue(DiContainer $container, iterable $ids): void
    {
        foreach ($ids as $id) {
            if (!$container->has($id)) {
                throw new DomainException(
                    sprintf('Service "%s" not found.', $id)
                );
            }
        }
    }

    public static function ContainerZeroOnRandomIds(): Generator
    {
        $config = new DiContainerConfig(useZeroConfigurationDefinition: true);
        $container = (new DiContainerBuilder($config))
            ->addDefinitions(Fixtures::configuredDefinitions())
            ->build();

        yield 'random 10 services' => [
            $container,
            [...Fixtures::randomExistIds(), ...array_keys([...Fixtures::configuredDefinitions()])],
        ];
    }

    #[Benchmark]
    #[Parameters([self::class, 'ContainerZeroOffRandomIds'])]
    public function hasServiceWithImportAllZeroConfigFalse(DiContainer $container, iterable $ids): void
    {
        foreach ($ids as $id) {
            if (!$container->get($id)) {
                throw new DomainException(
                    sprintf('Service "%s" not found.', $id)
                );
            }
        }
    }

    public static function ContainerZeroOffRandomIds(): Generator
    {
        $config = new DiContainerConfig(useZeroConfigurationDefinition: false);

        $container = (new DiContainerBuilder($config))
            ->import('Fixtures\\', __DIR__.'/../Fixtures')
            ->addDefinitions(Fixtures::configuredDefinitions())
            ->build();

        yield 'random 10 services' => [
            $container,
            [...Fixtures::randomExistIds(), ...array_keys([...Fixtures::configuredDefinitions()])],
        ];
    }

    #[Benchmark]
    #[Parameters([self::class, 'CompiledContainerRandomIds'])]
    public function hasServiceOnCompiledContainer(DiContainer $container, iterable $ids, iterable $noneExistIds): void
    {
        foreach ($ids as $id) {
            if (!$container->has($id)) {
                throw new DomainException(
                    sprintf('Service "%s" not found.', $id)
                );
            }
        }

        foreach ($noneExistIds as $id) {
            if ($container->has($id)) {
                throw new DomainException(
                    sprintf('Service "%s" found.', $id)
                );
            }
        }
    }

    public static function CompiledContainerRandomIds(): Generator
    {
        $container = (new DiContainerBuilder(
            new DiContainerConfig(
                useZeroConfigurationDefinition: false,
            )
        ))
            ->import('Fixtures\\', __DIR__.'/../Fixtures')
            ->addDefinitions(
                Fixtures::configuredDefinitions()
            )
            ->compileToFile(__DIR__.'/../var', 'ContainerHas', options: ['force_rebuild' => true])
            ->build();

        yield 'random 10 services' => [
            $container,
            [...Fixtures::randomExistIds(), ...array_keys([...Fixtures::configuredDefinitions()])],
            Fixtures::randomNoneExistIds(),
        ];
    }
}
