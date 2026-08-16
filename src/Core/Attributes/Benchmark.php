<?php

declare(strict_types=1);

namespace Kaspi\Benchmark\Core\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class Benchmark
{
    /**
     * @param list<non-empty-string>|non-empty-string $beforeMethod the name of the method or a list of methods called before the benchmark method is executed
     * @param list<non-empty-string>|non-empty-string $afterMethod the name of the method or a list of methods called after the benchmark method is executed
     */
    public function __construct(
        public readonly string $description = '',
        public readonly int $priority = 0,
        public readonly false|int $iterations = false,
        public readonly array|string $beforeMethod = [],
        public readonly array|string $afterMethod = [],
    ) {}
}
