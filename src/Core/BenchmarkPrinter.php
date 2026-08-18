<?php

declare(strict_types=1);

namespace Kaspi\Benchmark\Core;

use function explode;
use function sprintf;
use function wordwrap;
use const PHP_EOL;

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

|     |                                                      |       |         Memory            |    Time     |
| No. | Benchmark description                                | Iter. +-------------+-------------+  execution  |
|     |                                                      |       |  Allocated  |    Peak     |             |
TABLEHEAD;

        return $this->tableLineSeparator() . $th . $this->tableLineSeparator();
    }

    public function tableLineSeparator(): string
    {
        return <<< LINESE

+-----+------------------------------------------------------+-------+-------------+-------------+-------------+
LINESE;

    }

    public function printEachGroup(): void
    {
        if (!isset($this->benchmarkResultsCollection)) {
            print "Benchmark results collection is empty.\n";

            return;
        }

        print $this->tableHeader();

        $currentPackageVersion = null;

        foreach ($this->benchmarkResultsCollection as $benchmarkResult) {
            if ($currentPackageVersion !== $benchmarkResult->packageVersion) {
                print sprintf("\n| %-108.108s |", $benchmarkResult->packageVersion);
                $currentPackageVersion = $benchmarkResult->packageVersion;
                print sprintf("\n+%'-110s+", '');
            }

            print sprintf("\n| %-108.108s |", $benchmarkResult->groupName);
            print $this->tableLineSeparator();

            $timeExecuteMemoryUsingSumItems =  $benchmarkResult->getTimeExecuteMemoryUsingSumItems();

            $n = 1;
            $formatResult = PHP_EOL . '| %-3.3s | %-52.52s | %-5.5s | %-11.11s | %-11.11s | %-11.11s |';

            foreach ($timeExecuteMemoryUsingSumItems as $benchmarkDescription => $timeExecuteMemoryUsingSum) {
                $description = explode("\n", wordwrap($benchmarkDescription, 52, cut_long_words: true));

                print sprintf(
                    $formatResult,
                    $n,
                    $description[0],
                    $timeExecuteMemoryUsingSum->iterations,
                    Formatter::formatBytes($timeExecuteMemoryUsingSum->memoryUsageUsage, 4),
                    Formatter::formatBytes($timeExecuteMemoryUsingSum->memoryPeakUsage, 4),
                    Formatter::formatTimeExecute($timeExecuteMemoryUsingSum->hrTime, 4),
                );

                for ($i = 1, $c = count($description); $i < $c; $i++) {
                    print sprintf($formatResult, '', $description[$i], '', '', '', '');
                }

                print $this->tableLineSeparator();
                $n++;
            }
        }

        print "\n";
    }
}
