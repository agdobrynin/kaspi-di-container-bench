<?php

declare(strict_types=1);

namespace App;

use App\Services\Interfaces\ServiceInterface;

final class DoBench extends DoBenchAbstract
{
    private string $tag1 = 'tags.name_bar';
    private string $interfaceName = ServiceInterface::class;

    #[Benchmark(
        'First call for tag "tags.name_bar"',
        priority: 101,
    )]
    public function firstCallFindTagName1(): void
    {
        [...$this->container->findTaggedDefinitions($this->tag1)];
    }

    #[Benchmark(
        'Second call for tag "tags.name_bar"',
        priority: 100,
    )]
    public function secondCallFindTagName1(): void
    {
        [...$this->container->findTaggedDefinitions($this->tag1)];
    }

    #[Benchmark('Find via interface name "' . ServiceInterface::class . '"')]
    public function firstCallFindTagAsInterfaceName(): void
    {
        [...$this->container->findTaggedDefinitions($this->interfaceName)];
    }
}
