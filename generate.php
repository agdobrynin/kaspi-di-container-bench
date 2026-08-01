<?php

$fixturesDir = [
    'services' => __DIR__ . '/src/Services/',
    'interfaces' => __DIR__ . '/src/Services/Interfaces/',
];

$classesCount = 1000;
$tagSuffix = [
    'foo',
    'bar',
];

$diConfigure = [];

file_put_contents(
    $fixturesDir['interfaces']. '/ServiceInterface.php',
    '<?php
namespace App\Services\Interfaces;

interface ServiceInterface {}
');

for($i=0; $i < $classesCount; $i++) {
    $autowire = '';
    if (0 === (\random_int(0, $classesCount) % 2)) {
        $suffix = $tagSuffix[random_int(0, \count($tagSuffix) - 1)];
        $tag = '\'tags.name_'.$suffix.'\'';
        $autowire = <<< AUTOWIRE
use Kaspi\DiContainer\Attributes\{Autowire, Tag};

#[Autowire(tags: new Tag($tag))]
AUTOWIRE;
        $interface = '';
    } else {
        $interface = 'implements \App\Services\Interfaces\ServiceInterface';
    }

    $serviceName = 'Service'.$i;
    $diConfigure[] = 'App\Services\\'.$serviceName;

    $template = <<< TMPL
<?php
declare(strict_types=1);

namespace App\Services;
$autowire
final class  $serviceName $interface
{}

TMPL;
    file_put_contents($fixturesDir['services'].'/'.$serviceName.'.php', $template);

}

shuffle($diConfigure);

$config = '<?php
use function Kaspi\DiContainer\diAutowire;

return static function (): \Generator {'.PHP_EOL;

foreach ($diConfigure as $class) {
    $config .= \sprintf('   yield diAutowire(%s::class);'.PHP_EOL, $class);
}
$config.='};'.PHP_EOL;

file_put_contents($fixturesDir['services'].'/_di_config.php', $config);

print "\n \033[1;32m📁 The fixtures were successfully generated.\033[0m\n\n";