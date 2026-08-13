<?php

declare(strict_types=1);

namespace Kaspi\Benchmark\Core;

final class BenchmarkPrinter
{
    public function __construct(private readonly BenchmarkResults $benchmarkResults) {}

    public function print(): void
    {
        print <<< TABLEHEAD

+-----+---------------------------------------------------+-------+-----------------------+----------------+
|     |                                                   |       |         Memory        |                |
| No. | Benchmark description                             | Iter. +-----------+-----------+ Time execution |
|     |                                                   |       | Allocated |   Peak    |                |
+-----+-------+---------------------------------------------------+-----------+-----------+----------------|

TABLEHEAD;
        $timeExecuteMemoryUseingSumItems = $this->benchmarkResults->getTimeExecuteMemoryUsingSumItems();
        $n = 1;

        foreach ($timeExecuteMemoryUseingSumItems as $benchmarkDescription => $timeExecuteMemoryUsingSum) {
            $no = str_pad($n . '', 5, ' ', STR_PAD_BOTH);
            $iter = str_pad($timeExecuteMemoryUsingSum->iterations . '', 7, ' ', STR_PAD_BOTH);

            $description_cut = \strlen($benchmarkDescription) > 50 ?
                substr($benchmarkDescription, 0, 47) . '...'
                : $benchmarkDescription;
            $prepare_description = ' ' . str_pad($description_cut, 50);

            $net = str_pad(Formatter::formatBytes($timeExecuteMemoryUsingSum->memoryUsageUsage, 4), 11, ' ', STR_PAD_BOTH);
            $peak = str_pad(Formatter::formatBytes($timeExecuteMemoryUsingSum->memoryPeakUsage, 4), 11, ' ', STR_PAD_BOTH);
            $time = str_pad(Formatter::formatTimeExecute($timeExecuteMemoryUsingSum->hrTime, 4), 16, ' ', STR_PAD_BOTH);
            print <<< ROW
|$no|$prepare_description|$iter|$net|$peak|$time|
+-----+-----------------------------------------------------------+-----------+-----------+----------------+

ROW;
            $n++;
        }

        print "\n";
    }
}
