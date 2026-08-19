<?php

declare(strict_types=1);

namespace Kaspi\Benchmark;

use DomainException;
use Generator;
use Kaspi\Benchmark\Config\Configuration;
use Kaspi\Benchmark\Core\Attributes\Benchmark;
use Kaspi\Benchmark\Core\Attributes\Iterations;
use Kaspi\Benchmark\Core\Attributes\NumberOfTimes;
use Kaspi\Benchmark\Core\Attributes\Parameters;
use Kaspi\Benchmark\Core\BenchmarkAbstract;
use Kaspi\DiContainer\DiContainer;
use Kaspi\DiContainer\DiContainerBuilder;
use Kaspi\DiContainer\DiContainerConfig;
use function is_object;
use function sprintf;

#[Iterations(100)]
#[NumberOfTimes(10)]
final class DiContainerGet extends BenchmarkAbstract
{
    #[Benchmark]
    #[Parameters([self::class, 'ContainerZeroOnRandomIds'])]
    public function resolveServiceWithZeroConfigTrue(DiContainer $container, iterable $ids): void
    {
        foreach ($ids as $id) {
            $service = $container->get($id);

            if (!is_object($service)) {
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
            self::randomIdsArray(),
        ];
    }

    #[Benchmark]
    #[Parameters([self::class, 'ContainerZeroOffRandomIds'])]
    public function resolveServiceWithImportAllZeroConfigFalse(DiContainer $container, iterable $ids): void
    {
        foreach ($ids as $id) {
            $service = $container->get($id);

            if (!is_object($service)) {
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
            self::randomIdsArray(),
        ];
    }

    private static function randomIdsArray(): array
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
