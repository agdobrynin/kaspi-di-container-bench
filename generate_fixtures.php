<?php
declare(strict_types=1);

$fixtures = new class (
    __DIR__ . '/Fixtures/Services',
    __DIR__ . '/Fixtures/Services/Interfaces',
    'Fixtures\\Services',
    'Fixtures\\Services\\Interfaces',
    'Service',
    'ServiceInterface',
) {
    public function __construct(
        public readonly string $serviceSrc,
        public readonly string $interfaceSrc,
        public readonly string $serviceNamespace,
        public readonly string $interfaceNamespace,
        public readonly string $serviceNamePrefix,
        public readonly string $interfaceName,
    ) {}
};

// Generate interface
$fileInterface = sprintf('%s/%s.php', $fixtures->interfaceSrc, $fixtures->interfaceName);
$contentInterface = <<< CONTENT
<?php
declare(strict_types=1);

namespace $fixtures->interfaceNamespace;

interface $fixtures->interfaceName {}

CONTENT;

file_put_contents($fileInterface, $contentInterface);

/*
 * Make services
 */

$injectService = null;
$countOfService = 1000;

do {
    $serviceShortName = $fixtures->serviceNamePrefix.$countOfService;

    $autowireAttribute = '';
    $implementInterface = '';

    if (0 === (random_int(0, 100) % 2)) {
        $autowireAttribute = <<< AUTOWIRE
use Kaspi\DiContainer\Attributes\{Autowire, Tag};

#[Autowire(tags: new Tag('tags.name_bar'))]
AUTOWIRE;
    } else {
        $implementInterface = sprintf('implements \\%s\\%s', $fixtures->interfaceNamespace, $fixtures->interfaceName);
    }

    $template = <<< TMPL
<?php
declare(strict_types=1);

namespace $fixtures->serviceNamespace;
$autowireAttribute
final class $serviceShortName $implementInterface
{
    public function __construct($injectService) {}
}

TMPL;

    if (null === $injectService) {
        $injectService = sprintf('public readonly \\%s\\%s $service', $fixtures->serviceNamespace, $serviceShortName);
    }

    $serviceFile = sprintf('%s/%s.php', $fixtures->serviceSrc, $serviceShortName);

    file_put_contents($serviceFile, $template);

    $countOfService--;
} while ($countOfService > 0);

print "\n \033[1;32m📁 The fixtures were successfully generated.\033[0m\n\n";