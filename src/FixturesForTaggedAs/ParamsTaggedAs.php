<?php

declare(strict_types=1);

namespace App\FixturesForTaggedAs;

use Fixtures\Services\Interfaces\ServiceInterface;
use Kaspi\DiContainer\Attributes\TaggedAs;

final class ParamsTaggedAs
{
    public function __construct(
        #[TaggedAs('tags.name_bar')]
        public readonly iterable $taggedName,
        #[TaggedAs(ServiceInterface::class)]
        public readonly iterable $taggedInterface,
    ) {}
}
