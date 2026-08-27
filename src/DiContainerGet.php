<?php

declare(strict_types=1);

namespace App;

use App\FixturesForGet\ParamsRandomServices;
use App\FixturesForTaggedAs\ParamsTaggedAs;
use DomainException;
use Fixtures\Services\Service300;
use Generator;
use Kaspi\Benchmark\Attributes\Benchmark;
use Kaspi\Benchmark\Attributes\Group;
use Kaspi\Benchmark\Attributes\Iterations;
use Kaspi\Benchmark\Attributes\NumberOfTimes;
use Kaspi\Benchmark\Attributes\Parameters;
use Kaspi\DiContainer\DiContainer;
use Kaspi\DiContainer\DiContainerBuilder;
use Kaspi\DiContainer\DiContainerConfig;
use function is_object;
use function Kaspi\DiContainer\diAutowire;
use function sprintf;

#[Group('Get')]
#[Iterations(100)]
#[NumberOfTimes(2)]
final class DiContainerGet
{
    #[Benchmark('Resolve service')]
    #[Parameters([self::class, 'runtimeContainer'])]
    public function resolveServicesAtConstructor(DiContainer $runtimeContainer): void
    {
        if (!is_object($runtimeContainer->get(ParamsRandomServices::class))) {
            throw new DomainException(
                'Invalid initial service '.ParamsRandomServices::class
            );
        }
    }

    public static function runtimeContainer(): Generator
    {
        yield 'zero config and one alias' => [
            (new DiContainerBuilder(
                new DiContainerConfig(
                    useZeroConfigurationDefinition: true,
                    useAttribute: true,
                )
            ))
                ->addDefinitions([
                    'alias_of_service_300' => diAutowire(Service300::class),
                ])
                ->build()
        ];
    }

    #[Benchmark('Tagged arguments as lazy loading.')]
    #[Parameters([self::class, 'forTaggedParams'])]
    #[NumberOfTimes(20)]
    public function getServiceWithTaggedParameterAsTagName(DiContainer $container, string $id): void
    {
        if (!is_object($container->get($id))) {
            throw new DomainException(
                sprintf('Service "%s" not found.', $id)
            );
        }
    }

    public static function forTaggedParams(): Generator
    {
        $container = (new DiContainerBuilder(
            new DiContainerConfig(
                useZeroConfigurationDefinition: false,
            )
        ))
            ->compileToFile(__DIR__.'/../var', 'ContainerGetTaggedArg', options: ['force_rebuild' => true])
            ->import(
                'App\\',
                __DIR__.'/FixturesForTaggedAs',
                excludeFiles: ['ParamsRandomServices.php'],
            )
            ->import('Fixtures\\', __DIR__.'/../Fixtures')
            ->build()
        ;

        yield 'compiled container' => [
            $container,
            ParamsTaggedAs::class,
        ];

        $container = (new DiContainerBuilder(
            new DiContainerConfig(
                useZeroConfigurationDefinition: false,
            )
        ))
            ->import('App\\', __DIR__.'/FixturesForTaggedAs')
            ->import('Fixtures\\', __DIR__.'/../Fixtures')
            ->build()
        ;

        yield 'runtime container' => [
            $container,
            ParamsTaggedAs::class,
        ];
    }
}
