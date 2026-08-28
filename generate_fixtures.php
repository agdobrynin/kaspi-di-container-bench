<?php
declare(strict_types=1);

use Kaspi\Benchmark\Config\Configuration;

require_once __DIR__ . '/vendor/autoload.php';

// Generate interface
if ([] === glob(Configuration::InterfacesSrc->getValue().'/*.php')) {
    $fileInterface = sprintf('%s/%s.php', Configuration::InterfacesSrc->getValue(), Configuration::InterfaceName->getValue());
    $interfaceNamespace = Configuration::InterfacesNamespace->getValue();
    $interfaceName = Configuration::InterfaceName->getValue();

    $contentInterface = <<< CONTENT
<?php
declare(strict_types=1);

namespace $interfaceNamespace;

interface $interfaceName {}

CONTENT;

    file_put_contents($fileInterface, $contentInterface);
    print "\033[1;32m📁 The fixtures for interfaces were successfully generated.\033[0m\n";
} else {
    print "\033[1;33m📂 The fixtures for interfaces already exist.\033[0m\n";
}

/*
 * Make services
 */

$injectService = null;
$countOfService = Configuration::MaxIndexOfService->getValue();

if ($countOfService === count(glob(Configuration::ServicesSrc->getValue().'/*.php'))) {
    print "\033[1;33m📂 The fixtures for services already exist.\033[0m\n";
    exit(0);
}

$tagName = var_export('tags.name_bar', true);
$diClassesFQCN = [];

do {
    $serviceShortName = Configuration::ServicesNamePrefix->getValue().$countOfService;

    $autowireAttribute = '';
    $implementInterface = '';

    if (0 === (random_int(0, 100) % 2)) {
        $autowireAttribute = <<< AUTOWIRE
use Kaspi\DiContainer\Attributes\{Autowire, Tag};

#[Autowire(tags: new Tag($tagName))]
AUTOWIRE;
    } else {
        $implementInterface = sprintf('implements \\%s\\%s', Configuration::InterfacesNamespace->getValue(), Configuration::InterfaceName->getValue());
    }

    $servicesNamespace = Configuration::ServicesNamespace->getValue();
    $diClassesFQCN[] = sprintf('%s\\%s', $servicesNamespace, $serviceShortName);

    $template = <<< TMPL
<?php
declare(strict_types=1);

namespace $servicesNamespace;
$autowireAttribute
final class $serviceShortName $implementInterface
{
    public function __construct($injectService) {}
}

TMPL;

    if (null === $injectService) {
        $injectService = sprintf('public readonly %s $service', $serviceShortName);
    }

    $serviceFile = sprintf('%s/%s.php', Configuration::ServicesSrc->getValue(), $serviceShortName);

    file_put_contents($serviceFile, $template);

    $countOfService--;
} while ($countOfService > 0);

// generate di classes config
$classesAutowire = '';
foreach ($diClassesFQCN as $classFQCN) {
    $classesAutowire.=sprintf("    yield diAutowire(%s::class);\n", $classFQCN);
}

$diClassesConfigContent = <<< CONTENT
<?php
declare(strict_types=1);
use function \Kaspi\DiContainer\diAutowire;

return static function ():\Generator {
$classesAutowire    
};
CONTENT;

file_put_contents(Configuration::DiClassesConfigFile->getValue(), $diClassesConfigContent);

print "\033[1;32m📁 The fixtures for services were successfully generated.\033[0m\n";