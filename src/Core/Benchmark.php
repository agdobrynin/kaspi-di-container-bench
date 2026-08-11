<?php

declare(strict_types=1);

namespace Kaspi\Benchmark\Core;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class Benchmark
{
    public function __construct(
        public readonly string $description = '',
        public readonly int $priority = 0,
        public readonly int $iterations = 1,
        public readonly ?string $beforeMethod = null,
    ) {}
}
