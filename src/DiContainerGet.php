<?php

declare(strict_types=1);

namespace Kaspi\Benchmark;

use DomainException;
use Generator;
use Kaspi\Benchmark\Core\Attributes\Benchmark;
use Kaspi\Benchmark\Core\Attributes\Iterations;
use Kaspi\Benchmark\Core\Attributes\NumberOfTimes;
use Kaspi\Benchmark\Core\Attributes\Parameters;
use Kaspi\Benchmark\Core\BenchmarkAbstract;
use Kaspi\DiContainer\DiContainer;
use Kaspi\DiContainer\DiContainerBuilder;
use Kaspi\DiContainer\DiContainerConfig;
use Psr\Container\NotFoundExceptionInterface;
use function is_object;
use function sprintf;

#[Iterations(100)]
#[NumberOfTimes(2)]
final class DiContainerGet extends BenchmarkAbstract
{
    #[Benchmark]
    #[Parameters([self::class, 'ContainerZeroOnRandomIds'])]
    public function resolveServiceWithZeroConfigTrue(DiContainer $container, iterable $ids, iterable $noneExistIds): void
    {
        foreach ($ids as $id) {
            if (!is_object($container->get($id))) {
                throw new DomainException(
                    sprintf('Service "%s" not found.', $id)
                );
            }
        }

        foreach ($noneExistIds as $id) {
            try {
                $container->get($id);
            } catch (NotFoundExceptionInterface) {}
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
            Fixtures::randomNoneExistIds(),
        ];
    }

    #[Benchmark]
    #[Parameters([self::class, 'ContainerZeroOffRandomIds'])]
    public function resolveServiceWithImportAllZeroConfigFalse(DiContainer $container, iterable $ids, iterable $noneExistIds): void
    {
        foreach ($ids as $id) {
            if (!is_object($container->get($id))) {
                throw new DomainException(
                    sprintf('Service "%s" not found.', $id)
                );
            }
        }


        foreach ($noneExistIds as $id) {
            try {
                $container->get($id);
            } catch (NotFoundExceptionInterface) {}
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
            Fixtures::randomNoneExistIds(),
        ];
    }

    #[Benchmark]
    #[Parameters([self::class, 'CompiledContainerRandomIds'])]
    public function resolveServiceOnCompiledContainer(DiContainer $container, iterable $ids, iterable $noneExistIds): void
    {
        foreach ($ids as $id) {
            if (!is_object($container->get($id))) {
                throw new DomainException(
                    sprintf('Service "%s" not found.', $id)
                );
            }
        }


        foreach ($noneExistIds as $id) {
            try {
                $container->get($id);
            } catch (NotFoundExceptionInterface) {}
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
            ->compileToFile(__DIR__.'/../var', 'ContainerGet', options: ['force_rebuild' => true])
            ->addDefinitions(Fixtures::configuredDefinitions())
            ->build();

        yield 'random 10 services' => [
            $container,
            [...Fixtures::randomExistIds(), ...array_keys([...Fixtures::configuredDefinitions()])],
            Fixtures::randomNoneExistIds(),
        ];
    }
}
