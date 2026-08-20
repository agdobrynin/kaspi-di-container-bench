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
use function sprintf;

#[Iterations(100)]
#[NumberOfTimes(2)]
final class DiContainerHas extends BenchmarkAbstract
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
        $container = (new DiContainerBuilder($config))->build();

        yield 'random 10 services' => [
            $container,
            Fixtures::randomIdsArray(),
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
            ->build();

        yield 'random 10 services' => [
            $container,
            Fixtures::randomIdsArray(),
        ];
    }
}
