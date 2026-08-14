<?php

declare(strict_types=1);

namespace Kaspi\Benchmark\Core\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class AfterMethod
{
    public function __construct(public readonly string $afterMethod) {}
}
