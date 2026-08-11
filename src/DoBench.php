<?php

declare(strict_types=1);

namespace App;

use DomainException;
use Fixtures\Services\Interfaces\ServiceInterface;
use function sprintf;

final class DoBench extends DoBenchAbstract
{
    private string $tag1 = 'tags.name_bar';
    private string $interfaceName = ServiceInterface::class;

    #[Benchmark(
        'First call for tag "tags.name_bar"',
        priority: 101,
        iterations: 10,
    )]
    public function firstCallFindTagName1(): void
    {
        $definitions = [...$this->container->findTaggedDefinitions($this->tag1)];

        if ($definitions === []) {
            throw new DomainException(
                sprintf('Tag "%s" not found.', $this->tag1)
            );
        }
    }

    #[Benchmark(
        'Second call for tag "tags.name_bar"',
        priority: 100,
        iterations: 10,
    )]
    public function secondCallFindTagName1(): void
    {
        $definitions = [...$this->container->findTaggedDefinitions($this->tag1)];

        if ($definitions === []) {
            throw new DomainException(
                sprintf('Tag "%s" not found.', $this->tag1)
            );
        }
    }

    #[Benchmark(
        'Find via interface name "' . ServiceInterface::class . '"',
        iterations: 10,
    )]
    public function firstCallFindTagAsInterfaceName(): void
    {
        $definitions = [...$this->container->findTaggedDefinitions($this->interfaceName)];

        if ($definitions === []) {
            throw new DomainException(
                sprintf('Services implement "%s" not found.', $this->interfaceName)
            );
        }
    }
}
