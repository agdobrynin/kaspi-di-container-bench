<?php

declare(strict_types=1);

namespace App\FixturesForTaggedAs;

use Kaspi\DiContainer\Attributes\TaggedAs;

final class TaggedAsTagName
{
    public function __construct(
        #[TaggedAs('tags.name_bar')]
        public readonly iterable $taggedServices,
    ) {}
}
