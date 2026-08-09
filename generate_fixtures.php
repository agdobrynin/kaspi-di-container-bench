<?php

$fixturesDir = [
    'services' => __DIR__ . '/src/Services/',
    'interfaces' => __DIR__ . '/src/Services/Interfaces/',
];

$classServiceNamePrefix = 'Service';
$namespaceService = 'App\\Services';

$diConfigure = [];

file_put_contents(
    $fixturesDir['interfaces']. '/ServiceInterface.php',
    '<?php
namespace App\Services\Interfaces;

interface ServiceInterface {}
');

/*
 * Make services
 */

$injectService = null;
$countOfService = 1000;

do {
    $serviceShortName = $classServiceNamePrefix.$countOfService;

    $autowireAttribute = '';
    $implementInterface = '';

    if (0 === (random_int(0, 100) % 2)) {
        $autowireAttribute = <<< AUTOWIRE
use Kaspi\DiContainer\Attributes\{Autowire, Tag};

#[Autowire(tags: new Tag('tags.name_bar'))]
AUTOWIRE;
    } else {
        $implementInterface = 'implements \App\Services\Interfaces\ServiceInterface';
    }

    $template = <<< TMPL
<?php
declare(strict_types=1);

namespace $namespaceService;
$autowireAttribute
final class $serviceShortName $implementInterface
{
    public function __construct($injectService) {}
}

TMPL;

    if (null === $injectService) {
        $injectService = 'public readonly \\'.$namespaceService.'\\'.$serviceShortName.' $service';
    }

    file_put_contents($fixturesDir['services'].'/'.$serviceShortName.'.php', $template);

    $countOfService--;
} while ($countOfService > 0);

print "\n \033[1;32m📁 The fixtures were successfully generated.\033[0m\n\n";