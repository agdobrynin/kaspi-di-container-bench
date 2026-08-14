<?php

declare(strict_types=1);

namespace Kaspi\Benchmark\Core\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class BeforeMethod
{
    public function __construct(public readonly string $beforeMethod) {}
}
