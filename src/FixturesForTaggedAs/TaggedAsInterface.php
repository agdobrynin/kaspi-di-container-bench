<?php

declare(strict_types=1);

namespace App\FixturesForTaggedAs;

use Fixtures\Services\Interfaces\ServiceInterface;
use Kaspi\DiContainer\Attributes\TaggedAs;

final class TaggedAsInterface
{
    public function __construct(
        #[TaggedAs(ServiceInterface::class)]
        public readonly iterable $taggedServices,
    ) {}
}
