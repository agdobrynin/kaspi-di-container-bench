<?php

declare(strict_types=1);

namespace App;

use App\Services\Interfaces\ServiceInterface;
use function hrtime;
use function sprintf;

final class DoBench extends DoBenchAbstract
{
    public function doBenchmarkFindTaggedDefinitions(): void
    {
        $container = $this->container;
        $tag1 = 'tags.name_bar';

        self::getFunctionMemory(static function () use ($container, $tag1) {
            $s = hrtime(true);
            $taggedAsTag1 = [...$container->findTaggedDefinitions($tag1)];
            $label = sprintf('First call for tag "%s". Found %d tags.', $tag1, \count($taggedAsTag1));
            self::executionTime($s, $label);
        });
        print "\n";

        self::getFunctionMemory(static function () use ($container, $tag1) {
            $s = hrtime(true);
            $taggedAsTag1 = [...$container->findTaggedDefinitions($tag1)];
            $label = sprintf('Second call for tag "%s". Found %d tags.', $tag1, \count($taggedAsTag1));
            self::executionTime($s, $label, "\033[32m");
        });
        print "\n";

        $tag2 = 'tags.name_foo';

        self::getFunctionMemory(static function () use ($container, $tag2) {
            $s = hrtime(true);
            $taggedAsTag2 = [...$container->findTaggedDefinitions($tag2)];
            $label = sprintf('First call for tag "%s". Found %d tags.', $tag2, \count($taggedAsTag2));
            self::executionTime($s, $label, "\033[33m");
        });
        print "\n";

        self::getFunctionMemory(static function () use ($container, $tag2) {
            $s = hrtime(true);
            $taggedAsTag2 = [...$container->findTaggedDefinitions($tag2)];
            $label = sprintf('First call for tag "%s". Found %d tags.', $tag2, \count($taggedAsTag2));
            self::executionTime($s, $label, "\033[34m");
        });
        print "\n";

        self::getFunctionMemory(static function () use ($container) {
            $s = hrtime(true);
            $taggedAsTagInterface = [...$container->findTaggedDefinitions(ServiceInterface::class)];
            $label = sprintf('Find via interface "%s". Found %d tags.', ServiceInterface::class, \count($taggedAsTagInterface));
            self::executionTime($s, $label, "\033[35m");
        });
        print "\n";
    }
}
