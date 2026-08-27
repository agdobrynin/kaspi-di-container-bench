<?php

declare(strict_types=1);

namespace App\FixturesForGet;

use Fixtures\Services\Interfaces\ServiceInterface;
use Fixtures\Services\Service1;
use Fixtures\Services\Service1000;
use Fixtures\Services\Service2;
use Fixtures\Services\Service263;
use Fixtures\Services\Service300;
use Fixtures\Services\Service4;
use Fixtures\Services\Service420;
use Fixtures\Services\Service500;
use Fixtures\Services\Service790;
use Fixtures\Services\Service950;
use Fixtures\Services\Service999;
use Kaspi\DiContainer\Attributes\Inject;

final class ParamsRandomServices
{
    public function __construct(
        public readonly Service500 $service500,
        public readonly Service1 $service1,
        public readonly Service950 $service950,
        public readonly Service4 $service4,
        public readonly Service1000 $service1000,
        public readonly Service263 $service263,
        public readonly Service790 $service790,
        public readonly Service2 $service2,
        public readonly Service420 $service420,
        public readonly Service999 $service999,
        #[Inject(ServiceFoo::class)]
        public readonly ServiceInterface $service,
        #[Inject('alias_of_service_300')]
        public readonly Service300 $serviceAlias,
    ) {}
}
