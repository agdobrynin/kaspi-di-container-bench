<?php

declare(strict_types=1);

namespace Kaspi\Benchmark;

use DomainException;
use Fixtures\Services\Interfaces\ServiceInterface;
use Generator;
use Kaspi\Benchmark\Core\Attributes\Benchmark;
use Kaspi\Benchmark\Core\Attributes\Iterations;
use Kaspi\Benchmark\Core\Attributes\Parameters;
use Kaspi\Benchmark\Core\DoBenchAbstract;
use Kaspi\DiContainer\DiContainer;
use Kaspi\DiContainer\DiContainerBuilder;
use function sprintf;

#[Iterations(100)]
final class DiContainerFindTaggedDefinitions extends DoBenchAbstract
{
    public static function dataProvider(): Generator
    {
        $container = (new DiContainerBuilder())
            ->import('Fixtures\\', __DIR__.'/../Fixtures')
            ->build();

        yield 'tag name is "tags.name_bar"' => [
            $container,
            'tags.name_bar',
        ];

        yield 'classes implements interface' => [
            $container,
            ServiceInterface::class,
        ];
    }

    #[Benchmark]
    #[Parameters([self::class, 'dataProvider'])]
    public function findTaggedDefinitionsViaAttribute(DiContainer $container, string $tag): void
    {
        // first call
        $found = false;

        foreach ($container->findTaggedDefinitions($tag) as $def) {
            $found = true;
        }

        if (false === $found) {
            throw new DomainException(
                sprintf('Tag "%s" not found.', $tag)
            );
        }

        // second call
        $found = false;

        foreach ($container->findTaggedDefinitions($tag) as $def) {
            $found = true;
        }

        if (false === $found) {
            throw new DomainException(
                sprintf('Tag "%s" not found.', $tag)
            );
        }
    }
}
