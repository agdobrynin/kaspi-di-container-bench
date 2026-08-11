<?php

declare(strict_types=1);

namespace Kaspi\Benchmark\Core;

final class BenchmarkPrinter
{
    public function __construct(private readonly BenchmarkResults $benchmarkResults) {}

    public function print(): void
    {
        print <<< TABLEHEAD

+-----+-------+---------------------------------------------------+-----------------------+----------------+
|     |       |                                                   |     Memory usage      |                |
| No. | Iter. | Benchmark description                             +-----------+-----------+ Time execution |
|     |       |                                                   |    Net    |   Peak    |                |
+-----+-------+---------------------------------------------------+-----------+-----------+----------------|

TABLEHEAD;
        $avgResults = $this->benchmarkResults->getAvgResults();
        $n = 1;

        foreach ($avgResults as $benchmarkDescription => $timeExecuteMemoryUseAvg) {
            $no = str_pad($n . '', 5, ' ', STR_PAD_BOTH);
            $iter = str_pad($timeExecuteMemoryUseAvg->iterations . '', 7, ' ', STR_PAD_BOTH);

            $description_cut = \strlen($benchmarkDescription) > 50 ?
                substr($benchmarkDescription, 0, 47) . '...'
                : $benchmarkDescription;
            $prepare_description = ' ' . str_pad($description_cut, 50);

            $net = str_pad(Formatter::formatBytes($timeExecuteMemoryUseAvg->memoryUsageUsage, 4), 11, ' ', STR_PAD_BOTH);
            $peak = str_pad(Formatter::formatBytes($timeExecuteMemoryUseAvg->memoryPeakUsage, 4), 11, ' ', STR_PAD_BOTH);
            $time = str_pad(Formatter::formatTimeExecute($timeExecuteMemoryUseAvg->hrTime, 4), 16, ' ', STR_PAD_BOTH);
            print <<< ROW
|$no|$iter|$prepare_description|$net|$peak|$time|
+-----+-----------------------------------------------------------+-----------+-----------+----------------+

ROW;
            $n++;
        }

        print "\n";
    }
}
