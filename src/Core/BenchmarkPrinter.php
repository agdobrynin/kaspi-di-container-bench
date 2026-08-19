<?php

declare(strict_types=1);

namespace Kaspi\Benchmark\Core;

use function array_key_last;
use function array_shift;
use function count;
use function explode;
use function printf;
use function wordwrap;

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

    public function printEachVersion(): void
    {
        $this->collectionIsEmpty();

        $currentPackageVersion = null;
        $formatResult = "\n| %-3s | %-52s | %-5s | %-11s | %-11s | %-11s |";
        $formatTableLineSeparator = "\n+%'-5s+%'-54s+%'-7s+%'-13s+%'-13s+%'-13s+";

        printf($formatTableLineSeparator, '', '', '', '', '', '');

        print <<< TABLEHEAD

|     |                                                      |       |         Memory            |    Time     |
| No. | Benchmark description                                | Iter. +-------------+-------------+  execution  |
|     |                                                      |       |  Allocated  |    Peak     |             |
TABLEHEAD;

        printf($formatTableLineSeparator, '', '', '', '', '', '');


        foreach ($this->benchmarkResultsCollection as $benchmarkResult) {
            if ($currentPackageVersion !== $benchmarkResult->packageVersion) {
                printf("\n| %-108s |", $benchmarkResult->packageVersion);
                $currentPackageVersion = $benchmarkResult->packageVersion;
                printf("\n+%'-110s+", '');
            }

            printf("\n| %-108s |", $benchmarkResult->groupName);
            printf($formatTableLineSeparator, '', '', '', '', '', '');

            $timeExecuteMemoryUsingSumItems =  $benchmarkResult->getTimeExecuteMemoryUsingSumItems();

            $n = 1;

            foreach ($timeExecuteMemoryUsingSumItems as $benchmarkDescription => $timeExecuteMemoryUsingSum) {
                $description = explode("\n", wordwrap($benchmarkDescription, 52, cut_long_words: true));

                printf(
                    $formatResult,
                    $n,
                    $description[0],
                    $timeExecuteMemoryUsingSum->iterations,
                    Formatter::formatBytes($timeExecuteMemoryUsingSum->memoryUsageUsage, 4),
                    Formatter::formatBytes($timeExecuteMemoryUsingSum->memoryPeakUsage, 4),
                    Formatter::formatTimeExecute($timeExecuteMemoryUsingSum->hrTime, 4),
                );

                for ($i = 1, $c = count($description); $i < $c; $i++) {
                    printf($formatResult, '', $description[$i], '', '', '', '');
                }

                printf($formatTableLineSeparator, '', '', '', '', '', '');
                $n++;
            }
        }

        print "\n";
    }

    public function printCompareVersions(): void
    {
        $this->collectionIsEmpty();
        $tableResults = [];

        // collect results group by "benchmark group name", "benchmark description", "package version".
        foreach ($this->benchmarkResultsCollection as $benchmarkResult) {
            foreach ($benchmarkResult->getTimeExecuteMemoryUsingSumItems() as $benchmarkDescription => $timeExecuteMemoryUsingSum) {
                $tableResults[$benchmarkResult->groupName][$benchmarkDescription][$benchmarkResult->packageVersion] = $timeExecuteMemoryUsingSum;
            }
        }

        $formatGroup = "\n| %-108s |";
        $formatResult = "\n| %45s | %10s | %-5s | %-11s | %-11s | %-11s |";
        $formatDivResult = "\n| %45s +%'-12s+%'-7s+%'-13s+%'-13s+%'-13s+";
        $formatLineDescription = "\n| %45s |%-12s|%-7s|%-13s|%-13s|%-13s|";
        $formatLineBound = "\n+%'-47s+%'-12s+%'-7s+%'-13s+%'-13s+%'-13s+";

        print <<< TABLEHEAD
+-----+-----------------------------------------+------------+-------+-------------+-------------+-------------+
| Benchmarks group                              |    Package |       |         Memory            |    Time     |
|  ↘️  Benchmark description                    |    version | Iter. +-------------+-------------+  execution  |
|                                               |            |       |  Allocated  |    Peak     |             |
TABLEHEAD;

        printf($formatLineBound, '', '', '', '', '', '');

        foreach ($tableResults as $groupName => $benchmarkResults) {
            printf($formatGroup, $groupName);
            printf($formatLineBound, '', '', '', '', '', '');

            foreach ($benchmarkResults as $benchmarkDescription => $packageVersions) {
                $descriptionWrap = explode("\n", wordwrap($benchmarkDescription, 45, cut_long_words: true));
                $lastPackageVersion = array_key_last($packageVersions);

                foreach ($packageVersions as $packageVersion => $timeExecuteMemoryUsingSum) {
                    $descriptionWrapLine = array_shift($descriptionWrap);

                    printf(
                        $formatResult,
                        $descriptionWrapLine,
                        $packageVersion,
                        $timeExecuteMemoryUsingSum->iterations,
                        Formatter::formatBytes($timeExecuteMemoryUsingSum->memoryUsageUsage, 4),
                        Formatter::formatBytes($timeExecuteMemoryUsingSum->memoryPeakUsage, 4),
                        Formatter::formatTimeExecute($timeExecuteMemoryUsingSum->hrTime, 4),
                    );

                    if ($lastPackageVersion !== $packageVersion) {
                        $descriptionWrapLine = array_shift($descriptionWrap);
                        printf($formatDivResult, $descriptionWrapLine, '', '', '', '', '');
                    }
                }

                do {
                    $descriptionWrapLine = array_shift($descriptionWrap);
                    if (null === $descriptionWrapLine) {
                        printf($formatLineBound, '', '', '', '', '', '');
                    } else {
                        printf($formatLineDescription, $descriptionWrapLine, '', '', '', '', '');
                    }
                } while (null !== $descriptionWrapLine);

            }
        }

        print "\n";
    }

    private function collectionIsEmpty(): void
    {
        if (!isset($this->benchmarkResultsCollection)) {
            print "Benchmark results collection is empty.\n";

            exit(1);
        }
    }
}
