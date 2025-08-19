<?php

namespace MODX\CloudCLI\Commands\Extras;

use MODX\CloudCLI\Commands\Command;
use MODX\CloudCLI\Services\Extras;
use Symfony\Component\Console\Output\OutputInterface;

class Clean extends Command
{
    public function __invoke(OutputInterface $output, $verbose = false): void
    {
        if ($verbose) {
            $output->writeln("Checking for Package Updates.");
        }
        $packages = new Extras($this->modx);
        if ($verbose) {
            $packages->setOutput($output);
        }
        $packages->purge();
        if (!$verbose) {
            $output->writeln("Packages cleaned.");
        }
    }
}