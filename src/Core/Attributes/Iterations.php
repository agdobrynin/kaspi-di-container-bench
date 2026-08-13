<?php

declare(strict_types=1);

namespace Kaspi\Benchmark\Core\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class Iterations
{
    public function __construct(public readonly int $iterations = 1) {}
}
