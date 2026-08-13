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
        $timeExecuteMemoryUseStatisticItems = $this->benchmarkResults->getTimeExecuteMemoryUseStatisticItems();
        $n = 1;

        foreach ($timeExecuteMemoryUseStatisticItems as $benchmarkDescription => $timeExecuteMemoryUseStatistic) {
            $no = str_pad($n . '', 5, ' ', STR_PAD_BOTH);
            $iter = str_pad($timeExecuteMemoryUseStatistic->iterations . '', 7, ' ', STR_PAD_BOTH);

            $description_cut = \strlen($benchmarkDescription) > 50 ?
                substr($benchmarkDescription, 0, 47) . '...'
                : $benchmarkDescription;
            $prepare_description = ' ' . str_pad($description_cut, 50);

            $net = str_pad(Formatter::formatBytes($timeExecuteMemoryUseStatistic->memoryUsageUsage, 4), 11, ' ', STR_PAD_BOTH);
            $peak = str_pad(Formatter::formatBytes($timeExecuteMemoryUseStatistic->memoryPeakUsage, 4), 11, ' ', STR_PAD_BOTH);
            $time = str_pad(Formatter::formatTimeExecute($timeExecuteMemoryUseStatistic->hrTime, 4), 16, ' ', STR_PAD_BOTH);
            print <<< ROW
|$no|$prepare_description|$iter|$net|$peak|$time|
+-----+-----------------------------------------------------------+-----------+-----------+----------------+

ROW;
            $n++;
        }

        print "\n";
    }
}
