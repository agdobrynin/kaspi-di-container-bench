<?php

declare(strict_types=1);

namespace App;

use DomainException;
use Fixtures\Services\Interfaces\ServiceInterface;
use Generator;
use Kaspi\Benchmark\Attributes\AfterMethod;
use Kaspi\Benchmark\Attributes\BeforeMethod;
use Kaspi\Benchmark\Attributes\Benchmark;
use Kaspi\Benchmark\Attributes\Group;
use Kaspi\Benchmark\Attributes\Iterations;
use Kaspi\Benchmark\Attributes\NumberOfTimes;
use Kaspi\Benchmark\Attributes\Parameters;
use Kaspi\DiContainer\DiContainerBuilder;
use Kaspi\DiContainer\Interfaces\DiContainerInterface;
use function sprintf;

#[Group('Find tagged definitions')]
#[Iterations(100)]
#[NumberOfTimes(4)]
#[BeforeMethod('buildContainer')]
#[AfterMethod('unsetContainer')]
final class DiContainerFindTaggedDefinitions
{
    private DiContainerInterface $container;
    private function buildContainer(): void
    {
        $this->container = (new DiContainerBuilder())
            ->import('Fixtures\\', __DIR__.'/../Fixtures')
            ->import('App\FixturesForTaggedAs\\', __DIR__.'/FixturesForTaggedAs')
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
