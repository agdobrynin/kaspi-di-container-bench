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
use function shuffle;
use function sprintf;

#[Group('Has')]
#[Iterations(100)]
#[NumberOfTimes(20)]
final class DiContainerHas
{
    /**
     * @param DiContainer $container
     * @param iterable<non-empty-string, bool> $ids
     */
    #[Benchmark]
    #[Parameters([self::class, 'containerAndIds'])]
    public function hasService(DiContainer $container, iterable $ids): void
    {
        foreach ($ids as $id => $exist) {
            if ($exist !== $container->has($id)) {
                throw new DomainException(
                    sprintf('Invalid has for id "%s".', $id)
                );
            }
        }
    }


    public static function containerAndIds(): Generator
    {
        $keys = array_keys(Fixtures::randomExistIds() + Fixtures::randomNoneExistIds() + Fixtures::configuredDefinitions());
        shuffle($keys);
        $ids = [];

        foreach ($keys as $key) {
            $ids[$key] = isset(Fixtures::configuredDefinitions()[$key]) || isset(Fixtures::randomExistIds()[$key]);
        }

        yield 'runtime container and zero config' => [
            (new DiContainerBuilder(new DiContainerConfig(useZeroConfigurationDefinition: true)))
                ->addDefinitions(Fixtures::configuredDefinitions())
                ->build(),
            $ids,
        ];

        yield 'runtime container and import all' => [
            (new DiContainerBuilder(new DiContainerConfig(useZeroConfigurationDefinition: false)))
                ->import('Fixtures\\', __DIR__.'/../Fixtures')
                ->addDefinitions(Fixtures::configuredDefinitions())
                ->build(),
            $ids,
        ];

        yield 'compiled container' => [
            (new DiContainerBuilder(new DiContainerConfig(useZeroConfigurationDefinition: false)))
                ->import('Fixtures\\', __DIR__.'/../Fixtures')
                ->addDefinitions(Fixtures::configuredDefinitions())
                ->compileToFile(__DIR__.'/../var', 'ContainerHas', options: ['force_rebuild' => true])
                ->build(),
            $ids,
        ];
    }
}
