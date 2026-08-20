<?php

declare(strict_types=1);

namespace Kaspi\Benchmark;

use DomainException;
use Fixtures\Services\Interfaces\ServiceInterface;
use Generator;
use Kaspi\Benchmark\Core\Attributes\AfterMethod;
use Kaspi\Benchmark\Core\Attributes\BeforeMethod;
use Kaspi\Benchmark\Core\Attributes\Benchmark;
use Kaspi\Benchmark\Core\Attributes\Iterations;
use Kaspi\Benchmark\Core\Attributes\NumberOfTimes;
use Kaspi\Benchmark\Core\Attributes\Parameters;
use Kaspi\Benchmark\Core\BenchmarkAbstract;
use Kaspi\DiContainer\DiContainerBuilder;
use Kaspi\DiContainer\Interfaces\DiContainerInterface;
use function sprintf;

#[Iterations(100)]
#[NumberOfTimes(10)]
#[BeforeMethod('buildContainer')]
#[AfterMethod('unsetContainer')]
final class DiContainerFindTaggedDefinitions extends BenchmarkAbstract
{
    private DiContainerInterface $container;
    private function buildContainer(): void
    {
        $this->container = (new DiContainerBuilder())
            ->import('Fixtures\\', __DIR__.'/../Fixtures')
            ->build();
    }

    private function unsetContainer(): void
    {
        unset($this->container);
    }
    public static function dataProvider(): Generator
    {
        yield 'tag name is "tags.name_bar"' => [
            'tags.name_bar',
        ];

        yield 'classes implements interface' => [
            ServiceInterface::class,
        ];
    }

    #[Benchmark]
    #[Parameters([self::class, 'dataProvider'])]
    public function findTaggedDefinitions(string $tag): void
    {
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
}
