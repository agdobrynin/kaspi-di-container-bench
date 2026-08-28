<?php

declare(strict_types=1);

namespace App\FixturesForBuildAndGet;

use Fixtures\Services\Interfaces\ServiceInterface;
use Fixtures\Services\Service1;
use Fixtures\Services\Service1000;
use Fixtures\Services\Service300;
use Fixtures\Services\Service4;
use Fixtures\Services\Service406;
use Fixtures\Services\Service790;
use Kaspi\DiContainer\Attributes\Inject;
use Kaspi\DiContainer\Attributes\TaggedAs;

final class ParamsRandomServicesAndTagged
{
    public function __construct(
        public readonly Service1000 $service1000,
        public readonly Service1 $service1,
        public readonly Service406 $service406,
        public readonly Service4 $service4,
        public readonly Service790 $service790,
        #[Inject('alias_of_service_300')]
        public readonly Service300 $service300,
        #[Inject(FooService::class)]
        public readonly ServiceInterface $service,
        #[TaggedAs('tags.name_bar')]
        public readonly iterable $taggedAsBarServices,
        #[TaggedAs(ServiceInterface::class)]
        public readonly iterable $taggedAsInterfaceServices,
    ) {}
}
