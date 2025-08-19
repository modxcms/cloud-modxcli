<?php

namespace MODX\CloudCLI\Commands\Extras;

use MODX\CloudCLI\Commands\Command;
use MODX\CloudCLI\Services\Extras;
use Symfony\Component\Console\Helper\ProgressIndicator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Upgrade extends Command
{
    public function __invoke(InputInterface $input, OutputInterface $output): void
    {
        $verbose = $input->getOption('verbose');
        $clean = $input->getOption('clean');
        $packages = new Extras($this->modx);
        if ($verbose) {
            $progressIndicator = new ProgressIndicator($output);
            $packages->setProgress($progressIndicator);
            $progressIndicator->start("Checking for Package Updates.");
        }
        $updates = $packages->get(true);
        /** @var \MODX\Revolution\Transport\modTransportPackage $package */
        foreach ($updates as $package) {
            if ($verbose) {
                $progressIndicator->setMessage("Updating Package: ".$package->get('package_name'));
            }
            $update = $packages->checkUpdates($package)[0];
            if ($update) {
                try {
                    $packages->installLatest($package, $update['signature'], $update['location']);
                    if ($verbose) {
                        $progressIndicator->setMessage("Package Updated: ".$package->get('package_name'));
                    }
                } catch (\Exception $e) {
                    $progressIndicator->setMessage("Error Updating Package: ".$package->get('package_name'));
                    $progressIndicator->setMessage($e->getMessage());
                }
            }
        }

        if ($verbose) {
            if (count($updates) > 0) {
                $progressIndicator->finish("Package Updates Complete.");
            } else {
                $progressIndicator->finish("No Package Updates Found.");
            }
        }

        if (count($updates) == 0 && !$verbose) {
            $output->writeln("No Package Updates Found.");
        }

        if ($clean) {
            if ($verbose) {
                $progressIndicator->start("Cleaning Up.");
            }
            $packages->purge();
            if ($verbose) {
                $progressIndicator->finish("Cleanup Complete.");
            }
        }
    }
}