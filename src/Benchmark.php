<?php

declare(strict_types=1);

namespace App;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class Benchmark
{
    public function __construct(
        public readonly string $description = '',
        public readonly int    $priority = 0,
    )
    {
    }
}
