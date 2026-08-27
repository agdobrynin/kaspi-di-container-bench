<?php

declare(strict_types=1);

namespace App\FixturesForGet;

use Fixtures\Services\Interfaces\ServiceInterface;
use Fixtures\Services\Service1000;

final class ServiceFoo implements ServiceInterface
{
    public function __construct(public readonly Service1000 $service1000) {}
}
