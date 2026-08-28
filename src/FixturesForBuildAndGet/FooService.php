<?php

declare(strict_types=1);

namespace App\FixturesForBuildAndGet;

use Fixtures\Services\Interfaces\ServiceInterface;
use Fixtures\Services\Service1000;

final class FooService implements ServiceInterface
{
    public function __construct(public readonly Service1000 $service) {}
}
