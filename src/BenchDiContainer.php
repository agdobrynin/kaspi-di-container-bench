<?php

declare(strict_types=1);

namespace Kaspi\Benchmark;

use DomainException;
use Fixtures\Services\Interfaces\ServiceInterface;
use Kaspi\Benchmark\Config\Configuration;
use Kaspi\Benchmark\Core\Attributes\AfterMethod;
use Kaspi\Benchmark\Core\Attributes\BeforeMethod;
use Kaspi\Benchmark\Core\Attributes\Benchmark;
use Kaspi\Benchmark\Core\Attributes\Iterations;
use Kaspi\Benchmark\Core\DoBenchAbstract;
use Kaspi\DiContainer\DiContainerBuilder;
use Kaspi\DiContainer\DiContainerConfig;
use Kaspi\DiContainer\Interfaces\DiContainerInterface;
use function bin2hex;
use function count;
use function in_array;
use function is_object;
use function random_bytes;
use function random_int;
use function sprintf;

#[Iterations(100)]
final class BenchDiContainer extends DoBenchAbstract
{
    private DiContainerInterface $container;
    /**
     * @var list<array{0: int, 1: string}>
     */
    private array $tenServicesIds = [];

    private function containerBuilder(DiContainerConfig $config): DiContainerBuilder
    {
        return  (new DiContainerBuilder($config))
            ->import('Fixtures\\', __DIR__.'/../Fixtures');
    }

    private function buildContainerWithDefaultConfig(): void
    {
        $this->container = $this->containerBuilder(new DiContainerConfig())
            ->build();
    }

    private function unsetContainer(): void
    {
        unset($this->container);
    }

    #[Benchmark(
        'Find tagged definitions with tag name "tags.name_bar"',
        priority: 101,
    )]
    #[BeforeMethod('buildContainerWithDefaultConfig')]
    #[AfterMethod('unsetContainer')]
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
    )]
    #[BeforeMethod('buildContainerWithDefaultConfig')]
    #[AfterMethod('unsetContainer')]
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

    private function tenServicesIds(): void
    {
        $this->tenServicesIds = [];
        $has = [];

        while (count($has) < 10) {
            $num = random_int(1, Configuration::MaxIndexOfService->getValue());
            if (!in_array($num, $has, true)) {
                $this->tenServicesIds[] = [
                    $num,
                    bin2hex(random_bytes(5)),
                ];
                $has[] = $num;
            }
        }
    }

    private function unsetTenServicesIds(): void
    {
        unset($this->tenServicesIds);
    }

    private function buildContainerWithConfigIsSingletonFalse(): void
    {
        $this->container = $this->containerBuilder(new DiContainerConfig(
            isSingletonServiceDefault: false,
        ))
            ->build();
    }

    private function buildContainerWithConfigIsSingletonTrue(): void
    {
        $this->container = $this->containerBuilder(new DiContainerConfig(
            isSingletonServiceDefault: true,
        ))
            ->build();
    }

    #[Benchmark(priority: 10_100)]
    #[Iterations(500)]
    #[BeforeMethod(['tenServicesIds', 'buildContainerWithConfigIsSingletonFalse'])]
    #[AfterMethod(['unsetTenServicesIds', 'unsetContainer'])]
    public function getMethodRandomServiceWhenIsSingletonFalse(): void
    {
        $serviceWithNamespace = '\\'.Configuration::ServicesNamespace->getValue().'\\'.Configuration::ServicesNamePrefix->getValue();
        foreach ($this->tenServicesIds as [$num, ]) {
            $service = $this->container->get($serviceWithNamespace.$num);
            if (!is_object($service)) {
                throw new DomainException(
                    sprintf('Service "%s" not found.', $serviceWithNamespace.$num)
                );
            }
        }
    }

    #[Benchmark(priority: 10_050)]
    #[Iterations(500)]
    #[BeforeMethod(['tenServicesIds', 'buildContainerWithConfigIsSingletonTrue'])]
    #[AfterMethod(['unsetTenServicesIds', 'unsetContainer'])]
    public function getMethodRandomServiceWhenIsSingletonTrue(): void
    {
        $serviceWithNamespace = '\\'.Configuration::ServicesNamespace->getValue().'\\'.Configuration::ServicesNamePrefix->getValue();
        foreach ($this->tenServicesIds as [$num, ]) {
            $service = $this->container->get($serviceWithNamespace.$num);
            if (!is_object($service)) {
                throw new DomainException(
                    sprintf('Service "%s" not found.', $serviceWithNamespace.$num)
                );
            }
        }
    }

    #[Benchmark(priority: 10_010)]
    #[Iterations(500)]
    #[BeforeMethod(['tenServicesIds', 'buildContainerWithConfigIsSingletonFalse'])]
    #[AfterMethod(['unsetTenServicesIds', 'unsetContainer'])]
    public function hasMethodRandomServiceWhenIsSingletonFalse(): void
    {
        $serviceWithNamespace = '\\'.Configuration::ServicesNamespace->getValue().'\\'.Configuration::ServicesNamePrefix->getValue();
        foreach ($this->tenServicesIds as [$num, $str]) {
            $hasTrue = $this->container->has($serviceWithNamespace.$num);

            if (!$hasTrue) {
                throw new DomainException(
                    sprintf('Service "%s" not found.', $serviceWithNamespace.$num)
                );
            }

            $hasFalse = $this->container->has($serviceWithNamespace.$str);

            if ($hasFalse) {
                throw new DomainException(
                    sprintf('Fail `has()` for non exist service "%s".', $serviceWithNamespace.$str)
                );
            }
        }
    }

    private function buildContainerWithZeroConfig(): void
    {
        $this->container = (new DiContainerBuilder())
            ->build();
    }

    #[Benchmark]
    #[Iterations(500)]
    #[BeforeMethod(['tenServicesIds', 'buildContainerWithZeroConfig'])]
    #[AfterMethod(['unsetTenServicesIds', 'unsetContainer'])]
    public function testResolveServicesWithZeroConfig(): void
    {
        $serviceWithNamespace = '\\'.Configuration::ServicesNamespace->getValue().'\\'.Configuration::ServicesNamePrefix->getValue();

        foreach ($this->tenServicesIds as [$num, ]) {
            $service = $this->container->get($serviceWithNamespace.$num);
            if (!is_object($service)) {
                throw new DomainException(
                    sprintf('Service "%s" not found.', $serviceWithNamespace.$num)
                );
            }
        }
    }
}
