<?php

declare(strict_types=1);

namespace Kaspi\Benchmark\Core;

use function str_pad;
use const STR_PAD_BOTH;

final class BenchmarkPrinter
{
    /**
     * @var list<BenchmarkResults>
     */
    private array $benchmarkResultsCollection;
    public function attach(BenchmarkResults $benchmarkResults): self
    {
        $this->benchmarkResultsCollection[] = $benchmarkResults;

        return $this;
    }

    public function reset(): void
    {
        unset($this->benchmarkResultsCollection);
    }

    public function tableHeader(): string
    {
        $th = <<< TABLEHEAD

+-----+---------------------------------------------------+-------+-----------------------+----------------+
|     |                                                   |       |         Memory        |                |
| No. | Benchmark description                             | Iter. +-----------+-----------+ Time execution |
|     |                                                   |       | Allocated |   Peak    |                |
TABLEHEAD;

        return $th.$this->tableLineSeparator();
    }

    public function tableLineSeparator(): string
    {
        return <<< LINESE

+-----+-----------------------------------------------------------+-----------+-----------+----------------+
LINESE;

    }

    public function printEachGroup(): void
    {
        if (!isset($this->benchmarkResultsCollection)) {
            print "Benchmark results collection is empty.\n";

            return;
        }

        print $this->tableHeader();

        foreach ($this->benchmarkResultsCollection as $benchmarkResult) {
            $benchmarkGroup = str_pad($benchmarkResult->groupName, 106, ' ', STR_PAD_BOTH);
            print <<< BENCHMARK_GRPUP

|$benchmarkGroup|
BENCHMARK_GRPUP;

            print $this->tableLineSeparator();

            $timeExecuteMemoryUsingSumItems =  $benchmarkResult->getTimeExecuteMemoryUsingSumItems();

            $n = 1;

            foreach ($timeExecuteMemoryUsingSumItems as $benchmarkDescription => $timeExecuteMemoryUsingSum) {
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
ROW;
                print $this->tableLineSeparator();
                $n++;
            }
        }

        print "\n";
    }
}
