<?php

declare(strict_types=1);

namespace App;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class BenchmarkDescription
{
    public function __construct(public readonly string $description)
    {
    }
}
