<?php

namespace MODX\CloudCLI\Commands\Extras;

use MODX\CloudCLI\Commands\Command;
use MODX\CloudCLI\Services\Extras;
use Symfony\Component\Console\Helper\ProgressIndicator;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

class GetList extends Command
{
    public function __invoke(OutputInterface $output, $verbose = false, $updatesOnly = false, $limit = 20, $offset = 0): void
    {
        $packages = new Extras($this->modx);
        if ($verbose) {
            $progressIndicator = new ProgressIndicator($output);
            $packages->setProgress($progressIndicator);
            if ($updatesOnly) {
                $progressIndicator->start("Checking for Package Updates.");
            } else {
                $progressIndicator->start("Checking for installed packages.");
            }
        }
        $installed = $packages->get($updatesOnly, $limit, $offset);
        $count = $packages->getTotal();
        $table = new Table($output);
        $headers = [
            'Package',
            'Version'
        ];
        if ($verbose) {
            $headers = array_merge($headers,
                [
                    'Signature',
                    'Provider',
                    'Installed'
                ]
            );
        } else {
            $table->setStyle('compact');
        }
        $table->setHeaders($headers);
        foreach ($installed as $package) {
            $row = [
                $package->get('package_name') . ($package->get('has_updates') ? " (Update Available)" : ""),
                $package->get('version_major') . "." .
                $package->get('version_minor') . "." .
                $package->get('version_patch') . "." .
                $package->get('release')
            ];
            if ($verbose) {
                $row = array_merge($row,
                    [
                        $package->get('signature'),
                        $package->get('provider'),
                        $package->get('installed')
                    ]
                );
            }
            $table->addRow($row);
        }
        if ($verbose) {
            $progressIndicator->finish("Packages Fetched");
        }
        if ($count == 0) {
            $output->writeln("No Packages Found.");
        } else {
            $table->render();
            $start = $offset + 1;
            $through = $offset + $limit;
            if ($verbose) {
                if ($count > $limit || $through > $limit) {
                    $output->writeln("Showing $start through $through");
                }
                $output->writeln("Total Packages: " . $count);
            } elseif ($count > $limit || $through > $limit) {
                $output->writeln("$start-$through of $count");
            }
        }
    }
}