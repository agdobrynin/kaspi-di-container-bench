<?php

declare(strict_types=1);

namespace App;

use App\Services\Interfaces\ServiceInterface;

final class DoBench extends DoBenchAbstract
{
    private string $tag1 = 'tags.name_bar';
    private string $tag2 = 'tags.name_foo';

    private string $interfaceName = ServiceInterface::class;

    #[BenchmarkDescription('First call for tag "tags.name_bar"')]
    public function doBenchmarkFirstCallFindTagName1(): void
    {
        [...$this->container->findTaggedDefinitions($this->tag1)];
    }

    #[BenchmarkDescription('Second call for tag "tags.name_bar"')]
    public function doBenchmarkSecondCallFindTagName1(): void
    {
        [...$this->container->findTaggedDefinitions($this->tag1)];
    }

    #[BenchmarkDescription('Second call for tag "tags.name_foo"')]
    public function doBenchmarkFirstCallFindTagName2(): void
    {
        [...$this->container->findTaggedDefinitions($this->tag2)];
    }

    #[BenchmarkDescription('Second call for tag "tags.name_foo"')]
    public function doBenchmarkSecondCallFindTagName2(): void
    {
        [...$this->container->findTaggedDefinitions($this->tag2)];
    }

    #[BenchmarkDescription('Find via interface name "' . ServiceInterface::class . '"')]
    public function doBenchmarkFirstCallFindTagAsInterfaceName(): void
    {
        [...$this->container->findTaggedDefinitions($this->interfaceName)];
    }
}
