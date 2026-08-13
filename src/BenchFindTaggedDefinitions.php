<?php

declare(strict_types=1);

namespace Kaspi\Benchmark;

use DomainException;
use Fixtures\Services\Interfaces\ServiceInterface;
use Kaspi\Benchmark\Core\Attributes\Benchmark;
use Kaspi\Benchmark\Core\Attributes\Iterations;
use Kaspi\Benchmark\Core\DoBenchAbstract;
use Kaspi\DiContainer\DiContainerBuilder;
use Kaspi\DiContainer\Interfaces\DiContainerInterface;
use function sprintf;

#[Iterations(100)]
final class BenchFindTaggedDefinitions extends DoBenchAbstract
{
    private DiContainerInterface $container;

    private function buildContainer(): void
    {
        $this->container = (new DiContainerBuilder())
            ->import('Fixtures\\', __DIR__.'/../Fixtures')
            ->build();
    }

    #[Benchmark(
        'Find tagged definitions with tag name "tags.name_bar"',
        priority: 101,
        beforeMethod: 'buildContainer'
    )]
    public function findTaggedDefinitionsViaAttribute(): void
    {
        $tag = 'tags.name_bar';

        // first call
        $found = false;

        foreach ($this->container->findTaggedDefinitions($tag) as $def) {
            $found = true;
        }

        if (false === $found) {
            throw new DomainException(
                sprintf('Tag "%s" not found.', $tag)
            );
        }

        // second call
        $found = false;

        foreach ($this->container->findTaggedDefinitions($tag) as $def) {
            $found = true;
        }

        if (false === $found) {
            throw new DomainException(
                sprintf('Tag "%s" not found.', $tag)
            );
        }
    }

    #[Benchmark(
        'Find via interface name "' . ServiceInterface::class . '"',
        beforeMethod: 'buildContainer'
    )]
    public function findTaggedDefinitionsViaInterfaceName(): void
    {
        $interfaceName = ServiceInterface::class;

        // first call
        $found = false;

        foreach ($this->container->findTaggedDefinitions($interfaceName) as $def) {
            $found = true;
        }

        if (false === $found) {
            throw new DomainException(
                sprintf('Services implement "%s" not found.', $interfaceName)
            );
        }

        // second call
        $found = false;

        foreach ($this->container->findTaggedDefinitions($interfaceName) as $def) {
            $found = true;
        }

        if (false === $found) {
            throw new DomainException(
                sprintf('Services implement "%s" not found.', $interfaceName)
            );
        }
    }
}
