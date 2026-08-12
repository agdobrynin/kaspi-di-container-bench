<?php

declare(strict_types=1);

namespace Kaspi\Benchmark;

use Kaspi\Benchmark\Core\Benchmark;
use Kaspi\Benchmark\Core\DoBenchAbstract;
use DomainException;
use Fixtures\Services\Interfaces\ServiceInterface;
use Kaspi\DiContainer\DiContainerBuilder;
use Kaspi\DiContainer\Interfaces\DiContainerInterface;
use function sprintf;

final class BenchFindTaggedDefinitions extends DoBenchAbstract
{
    private DiContainerInterface $container;
    private string $tag1 = 'tags.name_bar';
    private string $interfaceName = ServiceInterface::class;

    private function buildContainer(): void
    {
        $this->container ??= (new DiContainerBuilder())
            ->import('Fixtures\\', __DIR__.'/../Fixtures')
            ->build();
    }

    #[Benchmark(
        'First call for tag "tags.name_bar"',
        priority: 101,
        iterations: 10,
        beforeMethod: 'buildContainer'
    )]
    public function firstCallFindTagName1(): void
    {
        $definitions = [...$this->container->findTaggedDefinitions($this->tag1)];

        if ($definitions === []) {
            throw new DomainException(
                sprintf('Tag "%s" not found.', $this->tag1)
            );
        }

        unset($definitions);
    }

    #[Benchmark(
        'Second call for tag "tags.name_bar"',
        priority: 100,
        iterations: 10,
        beforeMethod: 'buildContainer'
    )]
    public function secondCallFindTagName1(): void
    {
        $definitions = [...$this->container->findTaggedDefinitions($this->tag1)];

        if ($definitions === []) {
            throw new DomainException(
                sprintf('Tag "%s" not found.', $this->tag1)
            );
        }

        unset($definitions);
    }

    #[Benchmark(
        'Find via interface name "' . ServiceInterface::class . '"',
        iterations: 10,
        beforeMethod: 'buildContainer'
    )]
    public function firstCallFindTagAsInterfaceName(): void
    {
        $definitions = [...$this->container->findTaggedDefinitions($this->interfaceName)];

        if ($definitions === []) {
            throw new DomainException(
                sprintf('Services implement "%s" not found.', $this->interfaceName)
            );
        }

        unset($definitions);
    }
}
